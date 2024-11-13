<?php

/*
 * Copyright (C) 2022 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Drupal\json_forms\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\json_forms\Form\Util\FieldNameUtil;
use Drupal\json_forms\Form\Util\FormCallbackExecutor;
use Drupal\json_forms\Form\Validation\FormValidationMapperInterface;
use Drupal\json_forms\Form\Validation\FormValidatorInterface;
use Drupal\json_forms\JsonForms\Definition\DefinitionFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for JSON Forms.
 *
 * Subclasses should return the result of buildJsonFormsForm() in their
 * implementation of buildForm().
 *
 * @phpstan-consistent-constructor
 *
 * Note: Properties must not be private, otherwise they get lost when form state
 * is recovered from cache: https://www.drupal.org/project/drupal/issues/3097143
 *
 * @see self::buildJsonFormsForm()
 */
abstract class AbstractJsonFormsForm extends FormBase {

  public const FLAG_RECALCULATE_ONCHANGE = 1;

  public const INTERNAL_VALUES_KEY = '__';

  protected FormArrayFactoryInterface $formArrayFactory;

  protected FormValidatorInterface $formValidator;

  protected FormValidationMapperInterface $formValidationMapper;

  /**
   * {@inheritDoc}
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get(FormArrayFactoryInterface::class),
      $container->get(FormValidatorInterface::class), $container->get(FormValidationMapperInterface::class));
  }

  public function __construct(
    FormArrayFactoryInterface $formArrayFactory,
    FormValidatorInterface $formValidator,
    FormValidationMapperInterface $formValidationMapper
  ) {
    $this->formArrayFactory = $formArrayFactory;
    $this->formValidator = $formValidator;
    $this->formValidationMapper = $formValidationMapper;
  }

  /**
   * Subclasses should call this method in their implementation of buildForm().
   *
   * To build a form with existing data, set the data as temporary in the form
   * state until the form state is cached (but not later).
   *
   * @param array<int|string, mixed> $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   * @param \stdClass $jsonSchema
   * @param \stdClass $uiSchema
   *
   * @return array<int|string, mixed>
   *   Should be used as return value of buildForms().
   *
   * @throws \InvalidArgumentException
   *
   * @see \Drupal\Core\Form\FormInterface::buildForm()
   * @see FormStateInterface::setTemporary()
   * @see FormStateInterface::isCached()
   */
  protected function buildJsonFormsForm(
    array $form,
    FormStateInterface $formState,
    \stdClass $jsonSchema,
    \stdClass $uiSchema,
    int $flags = 0
  ): array {
    $recalculateOnChange = (bool) ($flags & self::FLAG_RECALCULATE_ONCHANGE);
    $formState->set('jsonSchema', $jsonSchema);
    $formState->set('uiSchema', $uiSchema);
    $formState->set('recalculateOnChange', $recalculateOnChange);

    if (new \stdClass() == $uiSchema) {
      return [];
    }

    $definition = DefinitionFactory::createDefinition($uiSchema, $jsonSchema);
    $form = $this->formArrayFactory->createFormArray($definition, $formState);
    // @phpstan-ignore-next-line
    $form['#attributes']['class'][] = 'json-forms';

    $form['#attached']['library'][] = 'json_forms/disable_buttons_on_ajax';
    $form['#attached']['library'][] = 'json_forms/vertical_tabs';

    return $form;
  }

  /**
   * @param array<int|string, mixed> $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  public function validateForm(array &$form, FormStateInterface $formState): void {
    if ($formState->isSubmitted() || $formState->isValidationEnforced()) {
      parent::validateForm($form, $formState);
      FormCallbackExecutor::executePreSchemaValidationCallbacks($formState);
      $validationResult = $this->formValidator->validate(
        // @phpstan-ignore-next-line
        $formState->get('jsonSchema'),
        $this->getSubmittedData($formState)
      );
      $this->formValidationMapper->mapErrors($validationResult, $formState);
      $this->formValidationMapper->mapData($validationResult, $formState);
    }
  }

  /**
   * @phpstan-return array<int|string, mixed>
   */
  public function calculateData(FormStateInterface $formState): array {
    $validationResult = $this->formValidator->validate(
      // @phpstan-ignore-next-line
      $formState->get('jsonSchema'),
      $this->getSubmittedData($formState)
    );

    return $validationResult->getData();
  }

  /**
   * Subclasses may override doGetSubmittedData()
   *
   * @return array<int|string, mixed>
   *   The values of the form state with keys converted to JSON schema names and
   *   without Drupal internal values such as form_id. So the returned array
   *   should only contain keys described in the JSON schema.
   *
   * @see doGetSubmittedData()
   */
  final protected function getSubmittedData(FormStateInterface $formState): array {
    $key = '__submittedData';
    if (!$formState->hasTemporaryValue($key)) {
      $formState->setTemporaryValue($key, $this->doGetSubmittedData($formState));
    }

    // @phpstan-ignore-next-line
    return $formState->getTemporaryValue($key);
  }

  /**
   * @return array<int|string, mixed>
   *   The values of the form state with keys converted to JSON schema names and
   *   without Drupal internal values such as form_id. So the returned array
   *   should only contain keys described in the JSON schema.
   */
  protected function doGetSubmittedData(FormStateInterface $formState): array {
    // Remove internal values (e.g. add buttons for array elements)
    $formState->unsetValue(self::INTERNAL_VALUES_KEY);

    // We cannot use $formState->cleanValues() because it also drops the submit
    // button value.
    $data = array_filter(
      $formState->getValues(),
      fn ($key) => !in_array($key, $formState->getCleanValueKeys(), TRUE),
      ARRAY_FILTER_USE_KEY
    );

    return FieldNameUtil::toJsonData($data);
  }

}
