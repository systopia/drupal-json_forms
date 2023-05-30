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

namespace Drupal\json_forms\Form\Layout;

use Assert\Assertion;
use Drupal\Core\Form\FormStateInterface;
use Drupal\json_forms\Form\AbstractConcreteFormArrayFactory;
use Drupal\json_forms\Form\FormArrayFactoryInterface;
use Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition;
use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;
use Drupal\json_forms\JsonForms\Definition\Layout\LayoutDefinition;

abstract class AbstractLayoutArrayFactory extends AbstractConcreteFormArrayFactory {

  /**
   * {@inheritDoc}
   */
  public function createFormArray(DefinitionInterface $definition,
    FormStateInterface $formState,
    FormArrayFactoryInterface $formArrayFactory
  ): array {
    Assertion::isInstanceOf($definition, LayoutDefinition::class);
    /** @var \Drupal\json_forms\JsonForms\Definition\Layout\LayoutDefinition $definition */
    $form = $this->createBasicFormArray($definition);

    return array_merge($form, $this->createElementsFormArray($definition, $formState, $formArrayFactory));
  }

  /**
   * @return array<int|string, mixed> The layout form array without elements.
   */
  abstract protected function createBasicFormArray(LayoutDefinition $definition): array;

  /**
   * @return array<int|string, mixed>
   *   A form array containing a form array for each layout element.
   *
   * @throws \InvalidArgumentException
   */
  protected function createElementsFormArray(LayoutDefinition $definition,
    FormStateInterface $formState,
    FormArrayFactoryInterface $formArrayFactory
  ): array {
    $form = [];
    foreach ($definition->getElements() as $index => $element) {
      if ($element instanceof ControlDefinition) {
        $key = $index . '-' . implode('_', $element->getPropertyPath());
      }
      else {
        $key = $index;
      }

      $form[$key] = $formArrayFactory->createFormArray($element, $formState);
    }

    return $form;
  }

}
