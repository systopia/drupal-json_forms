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

namespace Drupal\json_forms\Form\Control\Callbacks;

use Drupal\Core\Form\FormStateInterface;

final class NumberValueCallback {

  /**
   * @param array<int|string, mixed> $element
   * @param mixed $input
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *
   * @return mixed
   */
  public static function convert(array $element, $input, FormStateInterface $formState) {
    if (FALSE === $input || '' === $input) {
      $value = $element['#default_value'] ?? NULL;
      if (NULL === $value) {
        // Prevent empty string as value. Drupal sets an empty string in this
        // case if no value is set in the form state.
        $formState->setValueForElement($element, NULL);
      }

      return $value;
    }

    if ('integer' === $element['#_type'] && static::isIntegerish($input)) {
      /** @var float|int|string $input */
      return (int) $input;
    }

    return \is_numeric($input) ? (float) $input : $input;
  }

  /**
   * @param mixed $value
   *
   * @return bool
   */
  private static function isIntegerish($value): bool {
    if (\is_string($value)) {
      $value = \trim($value);
    }

    // @phpstan-ignore equal.notAllowed
    return \is_numeric($value) && $value == (string) $value;
  }

}
