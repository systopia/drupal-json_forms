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
use Drupal\json_forms\Form\Control\Callbacks\ValueElementValueCallback;
use Drupal\json_forms\Form\Control\Util\BasicFormPropertiesFactory;
use Drupal\json_forms\Form\FormArrayFactoryInterface;
use Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition;
use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;

class ValueArrayFactory extends AbstractConcreteFormArrayFactory {

  public const INTERNAL_VALUES_PROPERTY_KEY = 'internal';

  public static function getPriority(): int {
    return HiddenArrayFactory::getPriority() + 1;
  }

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

    $form = [
      '#type' => 'value',
      '#value_callback' => ValueElementValueCallback::class . '::convert',
    ] + BasicFormPropertiesFactory::createFieldProperties($definition, $formState);

    // The value is loaded from temporary values, so we have to persist it into
    // the form state storage. Otherwise, it would get lost.
    $formStatePropertyKey = array_merge([self::INTERNAL_VALUES_PROPERTY_KEY], $definition->getPropertyPath());
    if ($formState->has($formStatePropertyKey)) {
      $form['#value'] = $form['#default_value'] = $formState->get($formStatePropertyKey);
    }
    else {
      if (isset($form['#default_value'])) {
        $form['#value'] = $form['#default_value'];
      }
      else {
        $form['#default_value'] ??= $form['#value'] ??= NULL;
      }
      $formState->set($formStatePropertyKey, $form['#default_value']);
    }

    return $form;
  }

  public function supportsDefinition(DefinitionInterface $definition): bool {
    return $definition instanceof ControlDefinition
      && 'hidden' === $definition->getOptionsValue('type')
      && TRUE === $definition->getOptionsValue('internal');
  }

}
