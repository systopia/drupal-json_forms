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
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\json_forms\Form\AbstractConcreteFormArrayFactory;
use Drupal\json_forms\Form\Control\Util\BasicFormPropertiesFactory;
use Drupal\json_forms\Form\FormArrayFactoryInterface;
use Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition;
use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;

/**
 * Creates a form submit button.
 *
 * This is a custom JSONForms extension.
 */
final class SubmitButtonArrayFactory extends AbstractConcreteFormArrayFactory {

  use StringTranslationTrait;

  private static int $confirmCount = 0;

  public static function getPriority(): int {
    return SelectArrayFactory::getPriority() + 1;
  }

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
      '#type' => 'submit',
      '#value' => $definition->getLabel(),
      '#validate' => [__CLASS__ . '::onValidate'],
      // Override value set in BasicFormPropertiesFactory.
      '#limit_validation_errors' => NULL,
      '#_data' => $definition->getOptionsValue('data'),
    ] + BasicFormPropertiesFactory::createFieldProperties($definition, $formState);

    if (NULL !== $definition->getOptionsValue('confirm')) {
      // @phpstan-ignore-next-line
      $form['#attached']['library'][] = 'json_forms/confirm';
      $class = 'json-forms-confirm-' . self::$confirmCount++;
      // @phpstan-ignore-next-line
      $form['#attributes']['class'][] = $class;
      // @phpstan-ignore-next-line
      $form['#attached']['drupalSettings']['jsonFormsConfirm'][$class] = [
        'text' => $definition->getOptionsValue('confirm'),
      ];
    }

    return $form;
  }

  public function supportsDefinition(DefinitionInterface $definition): bool {
    return $definition instanceof ControlDefinition && 'submit' === $definition->getOptionsValue('type');
  }

  /**
   * @param array<int|string, mixed> $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  public static function onValidate(array &$form, FormStateInterface $formState): void {
    $triggeringElement = $formState->getTriggeringElement();
    Assertion::isArray($triggeringElement);

    // Drupal's submit button always sets the value "op", though we don't want
    // it.
    $formState->unsetValue('op');

    // Replace the submit button label (value) with the data defined in the UI
    // schema.
    $formState->setValue($triggeringElement['#parents'], $triggeringElement['#_data']);

    $formState->getFormObject()->validateForm($form, $formState);
  }

}
