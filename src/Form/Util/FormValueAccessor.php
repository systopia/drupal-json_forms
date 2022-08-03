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

namespace Drupal\json_forms\Form\Util;

final class FormValueAccessor {

  /**
   * @param array<int|string, mixed> $form
   * @param array<int|string> $propertyPath
   *
   * @return mixed
   */
  public static function getValue(array $form, array $propertyPath) {
    $subForm = self::getSubForm($form, $propertyPath);

    return $subForm['#value'] ?? NULL;
  }

  /**
   * @param array<int|string, mixed> $form
   * @param array<int|string> $propertyPath
   * @param mixed $value
   */
  public static function setValue(array &$form, array $propertyPath, $value): void {
    if (is_array($value)) {
      foreach ($value as $k => $v) {
        self::setValue($form, array_merge($propertyPath, [(string) $k]), $v);
      }
    }
    else {
      $subForm = &self::getSubForm($form, $propertyPath);
      if (NULL !== $subForm && !self::isButton($subForm)) {
        $subForm['#value'] = $value;
      }
    }
  }

  /**
   * @param array<int|string, mixed> $form
   * @param array<int|string> $propertyPath
   *
   * @return array<int|string, mixed>|null
   */
  private static function &getSubForm(array &$form, array $propertyPath): ?array {
    if (($form['#parents'] ?? NULL) === $propertyPath) {
      return $form;
    }

    foreach ($form as $key => &$value) {
      if ((is_string($key) && str_starts_with($key, '#')) || !is_array($value)) {
        continue;
      }

      $subForm = &self::getSubForm($value, $propertyPath);
      if (NULL !== $subForm) {
        return $subForm;
      }
    }

    $null = NULL;

    return $null;
  }

  /**
   * @param array<int|string, mixed> $form
   *
   * @return bool
   */
  private static function isButton(array $form): bool {
    return in_array($form['#type'] ?? NULL, ['button', 'submit'], TRUE);
  }

}
