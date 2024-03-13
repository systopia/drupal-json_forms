<?php

/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

/**
 * In PHP dots and spaces in form field names are converted to underscores. This
 * class provides methods to allow transformation between form field names and
 * JSON property names.
 *
 * @see https://www.php.net/manual/en/language.variables.external.php
 */
final class FieldNameUtil {

  private const REPLACEMENTS = [
    '.' => ':-:',
    ' ' => ':_:',
  ];

  /**
   * @phpstan-param array<int|string, mixed> $data JSON serializable.
   *
   * @phpstan-return array<int|string, mixed> JSON serializable.
   */
  public static function toFormData(array $data): array {
    $newData = [];
    foreach ($data as $key => $value) {
      if (is_string($key)) {
        $newKey = FieldNameUtil::toFormName($key);
      }
      else {
        $newKey = $key;
      }

      $newData[$newKey] = is_array($value) ? self::toFormData($value) : $value;
    }

    return $newData;
  }

  public static function toFormName(string $name): string {
    return str_replace(
      array_keys(self::REPLACEMENTS),
      array_values(self::REPLACEMENTS),
      $name
    );
  }

  /**
   * @phpstan-param array<int|string> $path JSON path.
   *
   * @phpstan-return array<int|string>
   *   Value to be used as #parents in form array.
   */
  public static function toFormParents(array $path): array {
    foreach ($path as &$pathElement) {
      if (is_string($pathElement)) {
        $pathElement = FieldNameUtil::toFormName($pathElement);
      }
    }

    return $path;
  }

  /**
   * @phpstan-param array<int|string, mixed> $data JSON serializable.
   *
   * @phpstan-return array<int|string, mixed> JSON serializable.
   */
  public static function toJsonData(array $data): array {
    $newData = [];
    foreach ($data as $key => $value) {
      if (is_string($key)) {
        $newKey = FieldNameUtil::toJsonName($key);
      }
      else {
        $newKey = $key;
      }

      $newData[$newKey] = is_array($value) ? self::toJsonData($value) : $value;
    }

    return $newData;
  }

  public static function toJsonName(string $name): string {
    return str_replace(
      array_values(self::REPLACEMENTS),
      array_keys(self::REPLACEMENTS),
      $name
    );
  }

}
