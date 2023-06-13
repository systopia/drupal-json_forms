<?php

/*
 * Copyright (C) 2023 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Drupal\json_forms\Form\Control\Callbacks;

use Drupal\Core\Form\FormStateInterface;

final class SelectCallbacks {

  /**
   * @phpstan-param array{
   *   '#value': scalar,
   *   '#options': array<scalar, string>,
   *   '#required': bool
   * }&array<int|string, mixed> $element
   */
  public static function validate(array $element, FormStateInterface $formState): void {
    if (0 === $element['#value'] && isset($element['#options'][0]) && $element['#required']) {
      /*
       * 0 is treated as empty value by Drupal and validates "#required".
       * However if it is in the allowed options we want to accept it as valid
       * value. Limit validation errors is reset by Drupal after validating this
       * element.
       */
      $formState->setLimitValidationErrors([]);
    }
  }

  /**
   * @param array<int|string, mixed> $element
   * @param mixed $input
   *
   * @return mixed
   */
  public static function value(array &$element, $input, FormStateInterface $formState) {
    $value = self::getValue($element, $input, $formState);

    if (NULL === $value) {
      // Prevent empty string as value. Drupal sets an empty string in this
      // case if no value is set in the form state.
      $formState->setValueForElement($element, NULL);
    }

    return $value;
  }

  /**
   * @param array<int|string, mixed> $element
   * @param mixed $input
   *
   * @return mixed
   */
  private static function getValue(array &$element, $input, FormStateInterface $formState) {
    if (FALSE === $input) {
      return $element['#default_value'] ?? NULL;
    }

    // @phpstan-ignore-next-line
    if (array_key_exists('#empty_value', $element) && $input === (string) $element['#empty_value']) {
      return '' === $element['#empty_value'] ? NULL : $element['#empty_value'];
    }

    // @phpstan-ignore-next-line
    foreach (array_keys($element['#options']) as $option) {
      if ($input === (string) $option) {
        return $option;
      }
    }

    return $input;
  }

}
