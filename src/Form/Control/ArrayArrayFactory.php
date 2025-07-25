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

namespace Drupal\json_forms\Form\Control;

use Assert\Assertion;
use Drupal\Core\Form\FormStateInterface;
use Drupal\json_forms\Form\AbstractConcreteFormArrayFactory;
use Drupal\json_forms\Form\AbstractJsonFormsForm;
use Drupal\json_forms\Form\Control\Callbacks\ArrayCallbacks;
use Drupal\json_forms\Form\Control\Util\BasicFormPropertiesFactory;
use Drupal\json_forms\Form\FormArrayFactoryInterface;
use Drupal\json_forms\Form\Util\FormStatePropertyAccessor;
use Drupal\json_forms\JsonForms\Definition\Control\ArrayControlDefinition;
use Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition;
use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;
use Drupal\json_forms\JsonForms\Definition\Layout\ArrayLayoutDefinition;
use Drupal\json_forms\JsonForms\Definition\Layout\LayoutDefinition;

final class ArrayArrayFactory extends AbstractConcreteFormArrayFactory {

  /**
   * {@inheritDoc}
   *
   * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
   */
  public function createFormArray(
  // phpcs:enable
    DefinitionInterface $definition,
    FormStateInterface $formState,
    FormArrayFactoryInterface $formArrayFactory
  ): array {
    Assertion::isInstanceOf($definition, ControlDefinition::class);
    /** @var \Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition $definition */
    $definition = ArrayControlDefinition::fromDefinition($definition);
    /** @var \Drupal\json_forms\JsonForms\Definition\Control\ArrayControlDefinition $definition */

    $fieldsetWrapperId = 'array-wrapper-' . str_replace('.', '__', implode('_', $definition->getPropertyPath()));
    // phpcs:disable Drupal.Commenting.InlineComment.DocBlock
    /** @phpstan-var array<int|string, mixed>&array{items: array<int, mixed>} $form */
    // phpcs:enable
    $form = [
      '#type' => 'fieldset',
      '#description_display' => 'before',
      '#prefix' => sprintf('<div id="%s" class="json-forms-array-wrapper">', $fieldsetWrapperId),
      '#suffix' => '</div>',
      'items' => [],
    ] + BasicFormPropertiesFactory::createBasicProperties($definition);

    if (TRUE === $definition->getOptionsValue('closeable')) {
      $form['#type'] = 'details';
      $form['#open'] = $definition->getOptionsValue('open', TRUE);
    }

    if ((!$formState->isCached() || $definition->isReadOnly())) {
      $items = $definition->getConst() ?? $formState->getTemporaryValue($definition->getPropertyPath())
        ?? $definition->getDefault();
      if (is_array($items)) {
        $items = array_map(fn ($item) => $item instanceof \stdClass ? (array) $item : $item, $items);
        $formState->setTemporaryValue($definition->getPropertyPath(), $items);
      }
    }

    $propertyAccessor = FormStatePropertyAccessor::create($formState, $definition->getPropertyFormParents());
    $numItems = $propertyAccessor->getProperty('numItems');
    if (NULL === $numItems) {
      $items ??= $formState->getTemporaryValue($definition->getPropertyPath());
      $numItems = is_array($items) ? count($items) : ($definition->getMinItems() ?? 0);
      $propertyAccessor->setProperty('numItems', $numItems);
    }
    else {
      Assertion::integer($numItems);
    }

    $internalParentsPrefix = array_merge(
      [AbstractJsonFormsForm::INTERNAL_VALUES_KEY],
      // @phpstan-ignore-next-line
      $form['#parents'],
    );

    if (0 === $numItems) {
      if (0 !== $definition->getMaxItems()) {
        // There might be a file field in the child elements. To ensure that the
        // form is sent as multipart/form-data in case a file field is
        // dynamically added, we claim that the form has a file element.
        // If there's no file field initially, the form would be sent as
        // application/x-www-form-urlencoded, i.e. file contents wouldn't be
        // submitted.
        $formState->setHasFileElement(TRUE);
      }

      // Ensure we get an empty array if there's no item.
      $form[] = [
        '#type' => 'hidden',
        '#value' => [],
        '#parents' => $definition->getPropertyFormParents(),
      ];
    }
    else {
      $arrayLayoutDefinition = $this->createLayoutDefinition($definition);
      if ('TableRow' === $arrayLayoutDefinition->getType()) {
        $form['items']['#type'] = 'table';
        $form['items']['#input'] = FALSE;
        $form['items']['#header'] = $this->buildTableHeader($arrayLayoutDefinition);
      }

      for ($i = 0; $i < $numItems; $i++) {
        $scopePrefix = $definition->getFullScope() . '/' . $i;
        $form['items'][$i] = $formArrayFactory->createFormArray(
          $arrayLayoutDefinition->withScopePrefix($scopePrefix),
          $formState
        );

        if (!$definition->isReadOnly()) {
          // Add remove button to item.
          $form['items'][$i]['__remove'] = [
            '#type' => 'button',
            '#disabled' => NULL !== $definition->getMinItems() && $numItems <= $definition->getMinItems(),
            '#value' => $definition->getOptionsValue('removeButtonLabel', 'x'),
            '#name' => $definition->getFullScope() . '_remove_' . $i,
            '#limit_validation_errors' => [],
            '#validate' => [ArrayCallbacks::class . '::removeItem'],
            '#submit' => [],
            '#ajax' => [
              'callback' => ArrayCallbacks::class . '::ajaxRemove',
              'wrapper' => $fieldsetWrapperId,
            ],
            '#parents' => array_merge($internalParentsPrefix, [$i, 'remove']),
            '#tree' => TRUE,
            '#_controlPropertyPath' => $definition->getPropertyFormParents(),
          ];
        }
      }
    }

    if (!$definition->isReadOnly()) {
      $form['__add'] = [
        '#type' => 'button',
        '#disabled' => NULL !== $definition->getMaxItems() && $numItems >= $definition->getMaxItems(),
        '#value' => $definition->getOptionsValue('addButtonLabel', '+'),
        '#limit_validation_errors' => [],
        '#validate' => [ArrayCallbacks::class . '::addItem'],
        '#submit' => [],
        '#ajax' => [
          'callback' => ArrayCallbacks::class . '::ajaxAdd',
          'wrapper' => $fieldsetWrapperId,
        ],
        '#parents' => array_merge($internalParentsPrefix, ['add']),
        '#tree' => TRUE,
        '#name' => $definition->getFullScope() . '_add',
        '#_controlPropertyPath' => $definition->getPropertyFormParents(),
      ];
    }

    return $form;
  }

  public function supportsDefinition(DefinitionInterface $definition): bool {
    return $definition instanceof ControlDefinition && 'array' === $definition->getType();
  }

  /**
   * @phpstan-return array<string|array<string, mixed>>
   */
  private function buildTableHeader(LayoutDefinition $arrayLayoutDefinition): array {
    $header = [];
    foreach ($arrayLayoutDefinition->getElements() as $element) {
      if ($element instanceof ControlDefinition) {
        if ('hidden' === $element->getOptionsValue('type')) {
          // Use no space for table cell.
          $label = ['style' => ['padding: 0;']];
        }
        else {
          $label = $element->getLabel();
          if ($element->isRequired()) {
            $label .= ' *';
          }
        }
      }
      else {
        $label = '';
      }

      $header[] = $label;
    }

    // Column for remove button.
    $header[] = '';

    return $header;
  }

  private function createLayoutDefinition(ArrayControlDefinition $definition): ArrayLayoutDefinition {
    // Note: We used to use "detail" as specification of the "normal" display,
    // not as it is described in JSON Forms:
    // https://jsonforms.io/docs/uischema/controls#the-detail-option
    // Now we use the option "elements" to specify individual controls for the
    // properties of an item and the option "itemLayout" to specify the layout
    // to display the elements.
    $detail = $definition->getOptionsValue('detail');
    Assertion::nullOrIsInstanceOf($detail, \stdClass::class);
    if (isset($detail->type) || isset($detail->elements)) {
      // phpcs:disable Drupal.Semantics.FunctionTriggerError.TriggerErrorTextLayoutRelaxed
      @trigger_error(<<<EOD
        The properties "type" and "elements" in option "detail" of an array
        control are deprecated and were never supported in accordance to the
        JSON Forms documentation. Use the options "itemLayout" and "elements"
        instead.
        EOD,
        E_USER_DEPRECATED
      );
      // phpcs:enable
    }

    $arrayUiSchema = new \stdClass();
    $arrayUiSchema->type = $definition->getOptionsValue('itemLayout') ?? $detail->type ?? 'TableRow';

    $items = $definition->getItems();
    Assertion::isInstanceOf($items, \stdClass::class);
    $arrayUiSchema->elements = $definition->getOptionsValue('elements') ?? $detail->elements
      ?? $this->createElementSchemas($items);

    return new ArrayLayoutDefinition(
      $arrayUiSchema, $items, $definition->isUiReadonly(), $definition->getRootDefinition()
    );
  }

  /**
   * @param \stdClass $items
   *
   * @return array<\stdClass>
   */
  private function createElementSchemas(\stdClass $items): array {
    if ('object' !== $items->type) {
      return [
        (object) [
          'type' => 'Control',
          'scope' => '#/',
        ],
      ];
    }

    $elements = [];
    foreach ($items->properties as $propertyName => $propertySchema) {
      $elements[] = (object) [
        'type' => 'Control',
        'scope' => '#/properties/' . $propertyName,
      ];
    }

    return $elements;
  }

}
