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

namespace Drupal\json_forms\Form\Control\Util;

use Drupal\Core\Form\FormStateInterface;
use Drupal\json_forms\Form\Control\Callbacks\RecalculateCallback;
use Drupal\json_forms\Form\Control\Rule\StatesArrayFactory;
use Drupal\json_forms\Form\Util\DescriptionDisplayUtil;
use Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition;

final class BasicFormPropertiesFactory {

  /**
   * Creates attributes for properties of elements in Drupal form for a Control.
   *
   * The properties are available in FormElement, and RenderElement used for
   * layouts.
   *
   * @phpstan-return array<string, mixed>
   */
  public static function createBasicProperties(ControlDefinition $definition): array {
    $form = [
      '#parents' => $definition->getPropertyFormParents(),
      '#title' => $definition->getLabel(),
      '#tree' => TRUE,
      '#_scope' => $definition->getFullScope(),
    ];

    if (NULL !== $definition->getDescription()) {
      $form['#description'] = $definition->getDescription();
    }

    // @phpstan-ignore argument.type
    DescriptionDisplayUtil::handleDescriptionDisplay($form, $definition->getOptionsValue('descriptionDisplay'));

    if (NULL !== $definition->getOptionsValue('placeholder')) {
      $form['#attributes']['placeholder'] = $definition->getOptionsValue('placeholder');
    }

    if (NULL !== $definition->getRule()) {
      $statesArrayFactory = new StatesArrayFactory();
      $form['#states'] = $statesArrayFactory->createStatesArray($definition->getRule());
    }

    // Custom option to hide labels, so they are not shown in the form by
    // default, but can be used in validation errors.
    if (TRUE === $definition->getOptionsValue('hideLabel')) {
      $form['#title_display'] = 'invisible';
    }

    return $form;
  }

  /**
   * Creates attributes for properties in Drupal FormElement for a Control.
   *
   * @phpstan-return array<string, mixed>
   */
  public static function createFieldProperties(ControlDefinition $definition, FormStateInterface $formState): array {
    $form = [
      '#disabled' => $definition->isReadOnly(),
      '#required' => $definition->isRequired(),
      '#limit_validation_errors' => [],
      '#_nullable' => $definition->isNullable(),
    ];

    if ((!$formState->isCached() || $definition->isReadOnly())
      && $formState->hasTemporaryValue($definition->getPropertyPath())) {
      $form['#default_value'] = $formState->getTemporaryValue($definition->getPropertyPath());
    }
    elseif (NULL !== $definition->getDefault()) {
      $form['#default_value'] = $definition->getDefault();
    }

    if (NULL !== $definition->getPrefix()) {
      $form['#field_prefix'] = $definition->getPrefix();
    }

    if (NULL !== $definition->getSuffix()) {
      $form['#field_suffix'] = $definition->getSuffix();
    }

    if (NULL !== $definition->getConst()) {
      $form['#value'] = $definition->getConst();
    }

    if (!$form['#disabled'] && TRUE === $formState->get('recalculateOnChange')) {
      $form['#ajax'] = [
        'callback' => RecalculateCallback::class . '::onChange',
        'event' => 'change',
        'progress' => [],
        'disable-refocus' => TRUE,
      ];
    }

    return array_merge(static::createBasicProperties($definition), $form);
  }

}
