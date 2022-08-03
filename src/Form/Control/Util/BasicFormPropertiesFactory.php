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

use Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition;

final class BasicFormPropertiesFactory {

  /**
   * Creates attributes for the properties available in Drupal FormElement.
   *
   * @param \Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition $definition
   *
   * @return array<string, mixed>
   */
  public static function createBasicProperties(ControlDefinition $definition): array {
    $form = [
      '#disabled' => $definition->isReadOnly(),
      '#required' => $definition->isRequired(),
      '#parents' => $definition->getPropertyPath(),
      '#title' => $definition->getLabel(),
      '#tree' => TRUE,
      '#_scope' => $definition->getFullScope(),
    ];

    if (NULL !== $definition->getDefault()) {
      $form['#default_value'] = $definition->getDefault();
    }

    if (NULL !== $definition->getDescription()) {
      $form['#description'] = $definition->getDescription();
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

    return $form;
  }

}
