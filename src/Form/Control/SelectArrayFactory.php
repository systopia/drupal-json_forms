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
use Drupal\json_forms\Form\ConcreteFormArrayFactoryInterface;
use Drupal\json_forms\Form\Control\Callbacks\NumberValueCallback;
use Drupal\json_forms\Form\Control\Util\BasicFormPropertiesFactory;
use Drupal\json_forms\Form\Control\Util\OptionsBuilder;
use Drupal\json_forms\Form\FormArrayFactoryInterface;
use Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition;
use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;

final class SelectArrayFactory implements ConcreteFormArrayFactoryInterface {

  /**
   * @inheritDoc
   */
  public function createFormArray(DefinitionInterface $definition,
    FormStateInterface $formState,
    FormArrayFactoryInterface $formArrayFactory
  ): array {
    Assertion::isInstanceOf($definition, ControlDefinition::class);
    /** @var \Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition $definition */
    $form = [
      '#type' => 'select',
      '#options' => OptionsBuilder::buildOptions($definition),
    ] + BasicFormPropertiesFactory::createFieldProperties($definition, $formState);

    if ($definition->getType() === 'number' || $definition->getType() === 'integer') {
      $form['#value_callback'] = NumberValueCallback::class . '::convert';
      $form['#_type'] = $definition->getType();
    }

    return $form;
  }

  public function supportsDefinition(DefinitionInterface $definition): bool {
    return $definition instanceof ControlDefinition
      && in_array($definition->getType(), ['string', 'number', 'integer'], TRUE)
      && (NULL !== $definition->getEnum() || NULL !== $definition->getOneOf());
  }

}
