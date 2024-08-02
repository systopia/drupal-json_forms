<?php

/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Drupal\json_forms\Form\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\json_forms\Form\FormArrayFactoryInterface;
use Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition;
use Drupal\json_forms\JsonForms\Definition\Custom\MarkupDefinition;
use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;
use Drupal\json_forms\JsonForms\Definition\Layout\LayoutDefinition;

/**
 * Creates an array for a row in a table render element.
 */
final class TableRowArrayFactory extends AbstractLayoutArrayFactory {

  public function supportsDefinition(DefinitionInterface $definition): bool {
    return $definition instanceof LayoutDefinition && 'TableRow' === $definition->getType();
  }

  protected function createBasicFormArray(LayoutDefinition $definition): array {
    return [];
  }

  protected function createElementFormArray(
    DefinitionInterface $element,
    FormStateInterface $formState,
    FormArrayFactoryInterface $formArrayFactory
  ): array {

    if ($element instanceof MarkupDefinition) {
      $label = $element->getMarkupSchema()->label ?? NULL;
      try {
        // If '#title_display' is set to 'hidden' the label should not be
        // visible, but the HTML tags may still use some space.
        $element->getMarkupSchema()->label = '';

        return parent::createElementFormArray($element, $formState, $formArrayFactory);
      }
      finally {
        $element->getMarkupSchema()->label = $label;
      }
    }

    if ($element instanceof ControlDefinition) {
      $description = $element->getControlSchema()->description ?? NULL;
      try {
        // The module "Form Tips" brings back label and description, even if
        // '#description_display' is set to 'invisible'. Thus, we explicitly set
        // an empty string here.
        $element->getControlSchema()->description = '';
        $form = ['#title_display' => 'invisible'];
        if ('hidden' === $element->getOptionsValue('type')) {
          // Use no space for table cell.
          $form['#wrapper_attributes']['style'][] = 'padding: 0;';
        }

        return $form + parent::createElementFormArray($element, $formState, $formArrayFactory);
      }
      finally {
        $element->getControlSchema()->description = $description;
      }
    }

    return [
      '#title_display' => 'invisible',
      '#description_display' => 'invisible',
    ] + parent::createElementFormArray($element, $formState, $formArrayFactory);
  }

}
