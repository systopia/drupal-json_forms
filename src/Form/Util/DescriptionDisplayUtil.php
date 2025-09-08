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

namespace Drupal\json_forms\Form\Util;

final class DescriptionDisplayUtil {

  /**
   * @phpstan-param array<int|string, mixed> $form
   */
  public static function handleDescriptionDisplay(array &$form, ?string $descriptionDisplay): void {
    if (NULL !== $descriptionDisplay) {
      // See module hooks.
      $form['#_json_forms_description_display'] = $descriptionDisplay;

      switch ($descriptionDisplay) {
        case 'after':
          $form['#description_display'] = 'after';
          break;

        case 'before':
          $form['#description_display'] = 'before';
          break;

        case 'invisible':
          $form['#description_display'] = 'invisible';
          break;
      }
    }
  }

}
