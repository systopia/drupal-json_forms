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
use Drupal\json_forms\Form\Control\Callbacks\StringValueCallback;
use Drupal\json_forms\Form\Control\Util\BasicFormPropertiesFactory;
use Drupal\json_forms\Form\FormArrayFactoryInterface;
use Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition;
use Drupal\json_forms\JsonForms\Definition\Control\StringControlDefinition;
use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;

class StringArrayFactory extends AbstractConcreteFormArrayFactory {

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
    $definition = StringControlDefinition::fromDefinition($definition);
    /** @var \Drupal\json_forms\JsonForms\Definition\Control\StringControlDefinition $definition */

    $form = [
      '#type' => TRUE === $definition->getOptionsValue('multi', FALSE) ? 'textarea' : 'textfield',
      '#value_callback' => StringValueCallback::class . '::convert',
    ] + BasicFormPropertiesFactory::createFieldProperties($definition, $formState);

    if (NULL !== $definition->getMaxLength()) {
      $form['#maxlength'] = $definition->getMaxLength();
    }

    if (NULL !== $definition->getPattern()) {
      $form['#pattern'] = $definition->getPattern();
    }

    return $form;
  }

  public function supportsDefinition(DefinitionInterface $definition): bool {
    return $definition instanceof ControlDefinition && 'string' === $definition->getType();
  }

}
