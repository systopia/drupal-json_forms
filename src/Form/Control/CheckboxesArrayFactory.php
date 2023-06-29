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
use Drupal\json_forms\Form\Control\Callbacks\CheckboxesCallbacks;
use Drupal\json_forms\Form\Control\Util\BasicFormPropertiesFactory;
use Drupal\json_forms\Form\Control\Util\OptionsBuilder;
use Drupal\json_forms\Form\FormArrayFactoryInterface;
use Drupal\json_forms\Form\Util\FormCallbackRegistrator;
use Drupal\json_forms\JsonForms\Definition\Control\ArrayControlDefinition;
use Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition;
use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;

final class CheckboxesArrayFactory extends AbstractConcreteFormArrayFactory {

  public static function getPriority(): int {
    return ArrayArrayFactory::getPriority() + 1;
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
    $definition = ArrayControlDefinition::fromDefinition($definition);
    /** @var \Drupal\json_forms\JsonForms\Definition\Control\ArrayControlDefinition $definition */
    $form = [
      '#type' => 'checkboxes',
      '#options' => OptionsBuilder::buildOptions($definition),
    ] + BasicFormPropertiesFactory::createFieldProperties($definition, $formState);

    if (isset($form['#default_value'])) {
      // @phpstan-ignore-next-line
      $form['#default_value'] = $this->convertToFormValue($form['#default_value']);
    }
    if (isset($form['#value'])) {
      // @phpstan-ignore-next-line
      $form['#value'] = $this->convertToFormValue($form['#value']);
    }

    FormCallbackRegistrator::registerPreSchemaValidationCallback(
      $formState,
      $definition->getFullScope(),
      [CheckboxesCallbacks::class, 'convertValue'],
      $form['#parents'],
    );

    return $form;
  }

  public function supportsDefinition(DefinitionInterface $definition): bool {
    if ($definition instanceof ControlDefinition && 'array' === $definition->getType()
      && TRUE === $definition->getPropertyKeywordValue('uniqueItems')) {
      $items = $definition->getPropertyKeywordValue('items');
      Assertion::nullOrIsInstanceOf($items, \stdClass::class);

      return NULL !== $items && (NULL !== ($items->enum ?? NULL) || NULL !== ($items->oneOf ?? NULL));
    }

    return FALSE;
  }

  /**
   * @phpstan-param array<int|string> $selectedOptions
   *
   * @phpstan-return array<int|string, int|string>
   */
  private function convertToFormValue(array $selectedOptions): array {
    return array_combine($selectedOptions, $selectedOptions);
  }

}
