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
use Drupal\json_forms\Form\Util\FormCallbackRegistrator;
use Drupal\json_forms\Form\Util\FormValidationUtil;
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
    return new static(
      $container->get(FormArrayFactoryInterface::class),
      $container->get(FormValidatorInterface::class),
      $container->get(FormValidationMapperInterface::class)
    );
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

    // @phpstan-ignore equal.notAllowed
    if (new \stdClass() == $uiSchema) {
      return [];
    }

    if (property_exists($jsonSchema, '$limitValidation')) {
      $formState->set('$limitValidationUsed', TRUE);
    }

    if ($formState->isRebuilding()) {
      $formState->set('$hasCalcInitField', FALSE);
      FormCallbackRegistrator::clearPreSchemaValidationCallbacks($formState);
    }

    $definition = DefinitionFactory::createDefinition($uiSchema, $jsonSchema);
    $form = $this->formArrayFactory->createFormArray($definition, $formState);

    if (TRUE === $formState->get('$limitValidationUsed')) {
      // Disable HTML form validation.
      // @phpstan-ignore offsetAccess.nonOffsetAccessible
      $form['#attributes']['novalidate'] = TRUE;
    }

    $form['#attributes']['class'][] = 'json-forms';

    $form['#attached']['library'][] = 'json_forms/disable_buttons_on_ajax';
    $form['#attached']['library'][] = 'json_forms/vertical_tabs';

    if (!$formState->isCached() || $formState->isRebuilding()) {
      if (TRUE === $formState->get('$calculateUsed')) {
        $form['#attached']['library'][] = 'json_forms/initial_calculation';
      }

      // Drupal prevents caching on safe methods.
      if (!$this->getRequest()->isMethodSafe()) {
        $formState->setCached();
      }
    }

    return $form;
  }

  /**
   * @param array<int|string, mixed> $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  public function validateForm(array &$form, FormStateInterface $formState): void {
    parent::validateForm($form, $formState);
    if ([] !== $formState->getErrors() && [] === $this->determineLimitValidationErrors($formState)) {
      // Even though the triggering element has #limit_validation_errors set to
      // [] form state might contain errors, e.g. if a no radio button of a
      // required radios element was selected. This might happen on
      // recalculation.
      $formState->clearErrors();
    }

    if ($formState->isSubmitted() || $formState->isValidationEnforced()) {
      FormCallbackExecutor::executePreSchemaValidationCallbacks($formState);
      if (TRUE === $formState->get('$limitValidationUsed')) {
        // We cannot use Drupal validation errors if the form uses limited
        // validation. They might contain errors that with the submitted data
        // would be ignored. (Avoiding Drupal validation is not possible on form
        // submit.)
        $keepFormErrorElementKeys = FormValidationUtil::getKeepFormErrorElementKeys($formState);
        $keepFormErrors = array_map(
          fn (array $elementKey) => $formState->getError(['#parents' => $elementKey]),
          $keepFormErrorElementKeys
        );
        $formState->clearErrors();
        foreach ($keepFormErrorElementKeys as $index => $elementKey) {
          if (NULL !== $keepFormErrors[$index]) {
            $element = ['#parents' => $elementKey];
            $formState->setError($element, $keepFormErrors[$index]);
          }
        }
      }
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

  /**
   * Copied from
   * \Drupal\Core\Form\FormValidator::determineLimitValidationErrors().
   *
   * Determines if validation errors should be limited.
   *
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The current state of the form.
   *
   * @return array<mixed>|null
   *
   * phpcs:disable Generic.Files.LineLength.TooLong
   */
  private function determineLimitValidationErrors(FormStateInterface $formState): ?array {
    // While this element is being validated, it may be desired that some
    // calls to \Drupal\Core\Form\FormStateInterface::setErrorByName() be
    // suppressed and not result in a form error, so that a button that
    // implements low-risk functionality (such as "Previous" or "Add more") that
    // doesn't require all user input to be valid can still have its submit
    // handlers triggered. The triggering element's #limit_validation_errors
    // property contains the information for which errors are needed, and all
    // other errors are to be suppressed. The #limit_validation_errors property
    // is ignored if submit handlers will run, but the element doesn't have a
    // #submit property, because it's too large a security risk to have any
    // invalid user input when executing form-level submit handlers.
    $triggering_element = $formState->getTriggeringElement();
    if (isset($triggering_element['#limit_validation_errors']) && ($triggering_element['#limit_validation_errors'] !== FALSE) && !($formState->isSubmitted() && !isset($triggering_element['#submit']))) {
      return $triggering_element['#limit_validation_errors'];
    }
    // If submit handlers won't run (due to the submission having been
    // triggered by an element whose #executes_submit_callback property isn't
    // TRUE), then it's safe to suppress all validation errors, and we do so
    // by default, which is particularly useful during an Ajax submission
    // triggered by a non-button. An element can override this default by
    // setting the #limit_validation_errors property. For button element
    // types, #limit_validation_errors defaults to FALSE, so that full
    // validation is their default behavior.
    elseif (NULL !== $triggering_element && !isset($triggering_element['#limit_validation_errors']) && !$formState->isSubmitted()) {
      return [];
    }
    // As an extra security measure, explicitly turn off error suppression if
    // one of the above conditions wasn't met. Since this is also done at the
    // end of this function, doing it here is only to handle the rare edge
    // case where a validate handler invokes form processing of another form.
    else {
      return NULL;
    }
    // phpcs:enable
  }

}
