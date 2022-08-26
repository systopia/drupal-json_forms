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

use Assert\Assertion;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\json_forms\Form\Validation\FormValidationMapperInterface;
use Drupal\json_forms\Form\Validation\FormValidatorInterface;
use Drupal\json_forms\JsonForms\Definition\DefinitionFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @phpstan-consistent-constructor
 *
 * Note: Properties must not be private, otherwise they get lost when form state
 * is recovered from cache: https://www.drupal.org/project/drupal/issues/3097143
 */
abstract class AbstractJsonFormsForm extends FormBase {

  public const FLAG_RECALCULATE_ONCHANGE = 1;

  public const INTERNAL_VALUES_KEY = '__';

  protected FormArrayFactoryInterface $formArrayFactory;

  protected FormValidatorInterface $formValidator;

  protected FormValidationMapperInterface $formValidationMapper;

  /**
   * @inheritDoc
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get(FormArrayFactoryInterface::class),
      $container->get(FormValidatorInterface::class), $container->get(FormValidationMapperInterface::class));
  }

  public function __construct(FormArrayFactoryInterface $formArrayFactory,
    FormValidatorInterface $formValidator,
    FormValidationMapperInterface $formValidationMapper
  ) {
    $this->formArrayFactory = $formArrayFactory;
    $this->formValidator = $formValidator;
    $this->formValidationMapper = $formValidationMapper;
  }

  /**
   * @inheritDoc
   *
   * @param array<int|string, mixed> $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Note: Using underscore case is enforced by Drupal's argument resolver.
   * @param \stdClass $jsonSchema
   * @param \stdClass $uiSchema
   *
   * @return array<int|string, mixed>
   *
   * @throws \InvalidArgumentException
   */
  public function buildForm(array $form,
    FormStateInterface $form_state,
    \stdClass $jsonSchema = NULL,
    \stdClass $uiSchema = NULL,
    int $flags = 0
  ): array {
    Assertion::notNull($jsonSchema);
    Assertion::notNull($uiSchema);

    $form_state->set('jsonSchema', $jsonSchema);
    $form_state->set('uiSchema', $uiSchema);
    $form_state->set('recalculateOnChange', (bool) ($flags & self::FLAG_RECALCULATE_ONCHANGE));

    if (new \stdClass() == $uiSchema) {
      return [];
    }

    $definition = DefinitionFactory::createDefinition($uiSchema, $jsonSchema);

    return $this->formArrayFactory->createFormArray($definition, $form_state);
  }

  /**
   * @param array<int|string, mixed> $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  public function validateForm(array &$form, FormStateInterface $formState): void {
    if ($formState->isSubmitted() || $formState->isValidationEnforced()) {
      parent::validateForm($form, $formState);
      $validationResult = $this->formValidator->validate($formState);
      $this->formValidationMapper->mapErrors($validationResult, $formState);
      $this->formValidationMapper->mapData($validationResult, $formState);
    }

    // Remove internal values (e.g. add buttons for array elements)
    $formState->unsetValue(self::INTERNAL_VALUES_KEY);
  }

  /**
   * @phpstan-return array<int|string, mixed>
   */
  public function calculateData(FormStateInterface $formState): array {
    $validationResult = $this->formValidator->validate($formState);

    return $validationResult->getData();
  }

  /**
   * @return array<int|string, mixed> The values of the form state without
   *   Drupal internal values such as form_id. So the returned array should
   *   only contain keys described in the JSON schema.
   */
  protected function getSubmittedData(FormStateInterface $formState): array {
    // We cannot use $formState->cleanValues() because it also drops the submit
    // button value.
    return array_filter(
      $formState->getValues(),
      fn ($key) => !in_array($key, $formState->getCleanValueKeys(), TRUE),
      ARRAY_FILTER_USE_KEY
    );
  }

}
