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

use Assert\Assertion;

final class JsonConverter {

  /**
   * @return array<int|string, mixed>
   *
   * @throws \JsonException
   */
  public static function toArray(\stdClass $data): array {
    $result = \json_decode(\json_encode($data, JSON_THROW_ON_ERROR), TRUE);
    Assertion::isArray($result);

    return $result;
  }

  /**
   * @param array<int|string, mixed> $data
   *
   * @throws \JsonException
   */
  public static function toStdClass(array $data): \stdClass {
    if ([] === $data) {
      return new \stdClass();
    }

    $result = \json_decode(\json_encode($data, JSON_THROW_ON_ERROR), FALSE, 512, JSON_THROW_ON_ERROR);
    Assertion::isInstanceOf($result, \stdClass::class);

    return $result;
  }

}
