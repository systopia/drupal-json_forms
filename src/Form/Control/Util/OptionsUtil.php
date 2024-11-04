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

use Drupal\json_forms\JsonForms\Definition\Control\ArrayControlDefinition;
use Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition;

final class OptionsUtil {

  /**
   * @param \Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition $definition
   *
   * @return array<string|int, string>
   *   Options for radio buttons or select fields. (Mapping of option value as
   *   string to label. Integerish strings are treated as integer when used as
   *   array key.)
   */
  public static function buildOptions(ControlDefinition $definition): array {
    $options = [];
    foreach (self::getEnum($definition) as $enum) {
      if (NULL !== $enum) {
        $options[self::optionToString($enum)] = (string) $enum;
      }
    }

    foreach (self::getOneOf($definition) as $option) {
      if (\property_exists($option, 'const') && NULL !== $option->const) {
        $options[self::optionToString($option->const)] = $option->title ?? (string) $option->const;
      }
    }

    return $options;
  }

  /**
   * @return array<string|int, scalar>
   *   Mapping of option value as string to actual option value. Integerish
   *   strings are treated as integer when used as array key.
   */
  public static function buildOptionValues(ControlDefinition $definition): array {
    $optionValues = [];
    foreach (self::getEnum($definition) as $enum) {
      if (NULL !== $enum) {
        $optionValues[self::optionToString($enum)] = $enum;
      }
    }

    foreach (self::getOneOf($definition) as $option) {
      if (\property_exists($option, 'const') && NULL !== $option->const) {
        $optionValues[self::optionToString($option->const)] = $option->const;
      }
    }

    return $optionValues;
  }

  /**
   * @return ''|null
   *   The actual value to use if the option with the empty value is selected.
   */
  public static function getEmptyOptionValue(ControlDefinition $definition): ?string {
    $enums = self::getEnum($definition);
    if (in_array('', $enums, TRUE)) {
      return '';
    }
    if (in_array(NULL, $enums, TRUE)) {
      return NULL;
    }

    foreach (self::getOneOf($definition) as $option) {
      if (\property_exists($option, 'const') && (NULL === $option->const || '' === $option->const)) {
        return $option->const;
      }
    }

    return NULL;
  }

  /**
   * @phpstan-return array<scalar|null>
   */
  private static function getEnum(ControlDefinition $definition): array {
    if ($definition instanceof ArrayControlDefinition) {
      $items = $definition->getItems();
      if (NULL === $items) {
        return [];
      }

      return $items->enum ?? [];
    }

    return $definition->getEnum() ?? [];
  }

  /**
   * @phpstan-return array<\stdClass>
   */
  private static function getOneOf(ControlDefinition $definition): array {
    if ($definition instanceof ArrayControlDefinition) {
      $items = $definition->getItems();
      if (NULL === $items) {
        return [];
      }

      return $items->oneOf ?? [];
    }

    return $definition->getOneOf() ?? [];
  }

  /**
   * Because the options are used in a HTML form we convert them to strings.
   *
   * @param scalar $option
   */
  private static function optionToString($option): string {
    if (FALSE === $option) {
      return '0';
    }

    return (string) $option;
  }

}
