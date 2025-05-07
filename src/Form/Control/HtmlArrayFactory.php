<?php

/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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
use Drupal\json_forms\Form\Control\Callbacks\HtmlCallbacks;
use Drupal\json_forms\Form\Control\Callbacks\StringValueCallback;
use Drupal\json_forms\Form\Control\Util\BasicFormPropertiesFactory;
use Drupal\json_forms\Form\FormArrayFactoryInterface;
use Drupal\json_forms\Form\Util\FormCallbackRegistrator;
use Drupal\json_forms\Form\Util\FormValidationUtil;
use Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition;
use Drupal\json_forms\JsonForms\Definition\Control\StringControlDefinition;
use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;

class HtmlArrayFactory extends AbstractConcreteFormArrayFactory {

  public static function getPriority(): int {
    return StringArrayFactory::getPriority() + 1;
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
    $definition = StringControlDefinition::fromDefinition($definition);
    /** @var \Drupal\json_forms\JsonForms\Definition\Control\StringControlDefinition $definition */

    $form = [
      '#type' => 'text_format',
      '#value_callback' => StringValueCallback::class . '::convert',
    ] + BasicFormPropertiesFactory::createFieldProperties($definition, $formState);

    if (NULL !== $definition->getMaxLength()) {
      $form['#maxlength'] = $definition->getMaxLength();
    }

    if (NULL !== $definition->getPattern()) {
      $form['#pattern'] = $definition->getPattern();
    }

    /** @var list<int|string> $elementKey */
    $elementKey = $form['#parents'];
    FormValidationUtil::addFormErrorMapping($formState, $elementKey, array_merge($elementKey, ['value']));
    FormValidationUtil::addKeepFormErrorElementKey($formState, array_merge($elementKey, ['format']));

    FormCallbackRegistrator::registerPreSchemaValidationCallback(
      $formState,
      $definition->getFullScope(),
      [HtmlCallbacks::class, 'convertValue'],
      $form['#parents'],
    );

    return $form;
  }

  public function supportsDefinition(DefinitionInterface $definition): bool {
    return $definition instanceof ControlDefinition && 'string' === $definition->getType()
      && 'text/html' === $definition->getContentMediaType();
  }

}
