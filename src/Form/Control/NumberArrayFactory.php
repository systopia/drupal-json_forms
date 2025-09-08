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
use Drupal\json_forms\Form\Control\Callbacks\NumberValueCallback;
use Drupal\json_forms\Form\Control\Util\BasicFormPropertiesFactory;
use Drupal\json_forms\Form\FormArrayFactoryInterface;
use Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition;
use Drupal\json_forms\JsonForms\Definition\Control\NumberControlDefinition;
use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;

final class NumberArrayFactory extends AbstractConcreteFormArrayFactory {

  /**
   * {@inheritDoc}
   */
  public function createFormArray(
    DefinitionInterface $definition,
    FormStateInterface $formState,
    FormArrayFactoryInterface $formArrayFactory
  ): array {
    Assertion::isInstanceOf($definition, ControlDefinition::class);
    $definition = NumberControlDefinition::fromDefinition($definition);

    $form = [
      '#type' => 'number',
      '#value_callback' => NumberValueCallback::class . '::convert',
      '#_type' => $definition->getType(),
      '#attached' => ['library' => ['json_forms/number_input']],
    ] + BasicFormPropertiesFactory::createFieldProperties($definition, $formState);

    if (NULL !== $definition->getExclusiveMinimum()) {
      $form['#min'] = $definition->getExclusiveMinimum() + \PHP_FLOAT_MIN;
    }
    elseif (NULL !== $definition->getMinimum()) {
      $form['#min'] = $definition->getMinimum();
    }

    if (NULL !== $definition->getExclusiveMaximum()) {
      $form['#max'] = $definition->getExclusiveMaximum() - \PHP_FLOAT_MIN;
    }
    elseif (NULL !== $definition->getMaximum()) {
      $form['#max'] = $definition->getMaximum();
    }

    if (NULL !== $definition->getPrecision()) {
      $form['#step'] = 1 / (10 ** $definition->getPrecision());
    }
    else {
      $form['#step'] = 'any';
    }

    return $form;
  }

  public function supportsDefinition(DefinitionInterface $definition): bool {
    return $definition instanceof ControlDefinition
      && ($definition->getType() === 'number' || $definition->getType() === 'integer');
  }

}
