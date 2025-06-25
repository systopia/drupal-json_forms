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

namespace Drupal\json_forms\JsonForms;

use Assert\Assertion;
use Opis\JsonSchema\JsonPointer;

final class ScopePointer {

  private JsonPointer $pointer;

  private string $scope;

  public static function new(string $scope): self {
    return new self($scope);
  }

  public function __construct(string $scope) {
    $this->scope = $scope;
    // Strip leading '#'.
    $pointer = JsonPointer::parse(substr($scope, 1));
    Assertion::notNull($pointer);
    $this->pointer = $pointer;
  }

  /**
   * @param \stdClass $jsonSchema
   *
   * @return \stdClass
   *
   * @throws \InvalidArgumentException
   *   If no schema was found.
   */
  public function getSchema(\stdClass $jsonSchema): \stdClass {
    $schema = $this->pointer->data($jsonSchema);
    if (NULL === $schema) {
      throw new \InvalidArgumentException(sprintf('No schema found for scope "%s"', $this->scope));
    }

    Assertion::isInstanceOf($schema, \stdClass::class);

    return $schema;
  }

  public function getParentPointer(): self {
    $pos = strrpos($this->scope, '/');
    Assertion::integer($pos);
    // Strip property name and "properties".
    $parentScope = substr($this->scope, 0, $pos - strlen('/properties'));
    if ('#' === $parentScope) {
      $parentScope = '#/';
    }

    return new self($parentScope);
  }

  /**
   * @return array<string|int>
   */
  public function getPath(): array {
    return $this->pointer->path();
  }

  /**
   * @return array<string|int>
   */
  public function getPropertyPath(): array {
    $consecutivePropertiesCount = 0;
    return array_values(array_filter(
      $this->getPath(),
      function ($value) use (&$consecutivePropertiesCount) {
        // There might be a property named "properties" and the corresponding
        // scope would contain ".../properties/properties/...". Thus, we cannot
        // just filter out every occurrence of "properties".
        if ('properties' === $value) {
          $consecutivePropertiesCount++;

          return $consecutivePropertiesCount % 2 === 0;
        }

        $consecutivePropertiesCount = 0;

        return '' !== $value;
      }
    ));
  }

  public function getScope(): string {
    return $this->scope;
  }

}
