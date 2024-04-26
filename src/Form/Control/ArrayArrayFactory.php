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
use Drupal\json_forms\JsonForms\Definition\Layout\LayoutDefinition;

final class ArrayArrayFactory extends AbstractConcreteFormArrayFactory {

  /**
   * {@inheritDoc}
   */
  public function createFormArray(
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

    $propertyAccessor = FormStatePropertyAccessor::create($formState, $definition->getPropertyFormParents());
    $numItems = $propertyAccessor->getProperty('numItems');
    if (NULL === $numItems) {
      $items = $formState->getTemporaryValue($definition->getPropertyPath());
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
            '#limit_validation_errors' => TRUE,
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
        '#limit_validation_errors' => TRUE,
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

  private function createLayoutDefinition(ArrayControlDefinition $definition): LayoutDefinition {
    // Note: We actually do not use "detail" as it is described in JSON Forms:
    // https://jsonforms.io/docs/uischema/controls#the-detail-option
    // Should we use another option name instead?
    $arrayUiSchema = $definition->getOptionsValue('detail');
    if (!$arrayUiSchema instanceof \stdClass) {
      $arrayUiSchema = new \stdClass();
    }

    $arrayUiSchema->type ??= 'TableRow';

    $items = $definition->getItems();
    Assertion::isInstanceOf($items, \stdClass::class);
    $arrayUiSchema->elements ??= $this->createElementSchemas($items);

    return new LayoutDefinition($arrayUiSchema, $items, $definition->isUiReadonly());
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
