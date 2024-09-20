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

declare(strict_types=1);

namespace Drupal\json_forms\Form\Control\Callbacks;

use Drupal\Core\Form\FormStateInterface;

final class SelectCallbacks {

  /**
   * @phpstan-param array{
   *   '#value': scalar,
   *   '#options': array<string|int, string>,
   *   '#required'?: bool
   * }&array<int|string, mixed> $element
   */
  public static function validate(array $element, FormStateInterface $formState): void {
    OptionValueCallbacks::validate($element, $formState);
  }

  /**
   * @param array<int|string, mixed> $element
   * @param mixed $input
   *
   * @return mixed
   */
  public static function value(array &$element, $input, FormStateInterface $formState) {
    if (array_key_exists('#empty_value', $element) && $input === (string) $element['#empty_value']) {
      $input = '' === $element['#empty_value'] ? NULL : $element['#empty_value'];

      if (NULL === $input) {
        // Prevent empty string as value. Drupal sets an empty string in this
        // case if no value is set in the form state.
        $formState->setValueForElement($element, NULL);
      }

      return $input;
    }

    return OptionValueCallbacks::value($element, $input, $formState);
  }

}
