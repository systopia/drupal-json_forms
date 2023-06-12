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
use Drupal\json_forms\Form\Control\Callbacks\SelectValueCallback;
use Drupal\json_forms\Form\Control\Util\BasicFormPropertiesFactory;
use Drupal\json_forms\Form\Control\Util\OptionsBuilder;
use Drupal\json_forms\Form\FormArrayFactoryInterface;
use Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition;
use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;

final class SelectArrayFactory extends AbstractConcreteFormArrayFactory {

  public static function getPriority(): int {
    return 10;
  }

  /**
   * {@inheritDoc}
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
      '#value_callback' => SelectValueCallback::class . '::convert',
    ] + BasicFormPropertiesFactory::createFieldProperties($definition, $formState);

    if (!$definition->isRequired()) {
      $form['#empty_value'] = '';
    }

    return $form;
  }

  public function supportsDefinition(DefinitionInterface $definition): bool {
    $allowedTypes = ['string', 'number', 'integer', 'boolean'];

    return $definition instanceof ControlDefinition
      && in_array($definition->getType(), $allowedTypes, TRUE)
      && (NULL !== $definition->getEnum() || NULL !== $definition->getOneOf());
  }

}
