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

final class OptionsBuilder {

  /**
   * @param \Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition $definition
   *
   * @return array<scalar|null, string> Options for radio buttons or select
   *   fields.
   */
  public static function buildOptions(ControlDefinition $definition): array {
    $options = [];
    foreach ($definition->getEnum() ?? [] as $enum) {
      if (NULL !== $enum) {
        $options[$enum] = (string) $enum;
      }
    }

    foreach ($definition->getOneOf() ?? [] as $option) {
      if (\property_exists($option, 'const')) {
        if (NULL !== $option->const) {
          $options[$option->const] = $option->title ?? (string) $option->const;
        }
      }
    }

    return $options;
  }

}
