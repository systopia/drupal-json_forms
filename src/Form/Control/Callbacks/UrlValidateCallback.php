<?php

/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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
use Drupal\Core\Render\Element\Url;

final class UrlValidateCallback {

  /**
   * @param array<int|string, mixed> $element
   * @param \Drupal\Core\Form\FormStateInterface $formState
   * @param array<int|string, mixed> $completeForm
   */
  public static function validate(array &$element, FormStateInterface $formState, array $completeForm): void {
    // @phpstan-ignore argument.type
    $value = $formState->getValue($element['#parents']);

    // Prevent Url::validateUrl() from setting an empty string when the current
    // value is NULL and allowed to be NULL.
    if (NULL !== $value || TRUE !== $element['#_nullable']) {
      Url::validateUrl($element, $formState, $completeForm);
    }
  }

}
