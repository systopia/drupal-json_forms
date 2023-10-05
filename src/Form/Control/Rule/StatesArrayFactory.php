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

namespace Drupal\json_forms\Form\Control\Rule;

use Drupal\json_forms\Form\Control\Util\FormPropertyUtil;
use Drupal\json_forms\JsonForms\ScopePointer;

final class StatesArrayFactory implements StatesArrayFactoryInterface {

  private StatesBuilder $statesBuilder;

  public function __construct() {
    $this->statesBuilder = new StatesBuilder();
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  public function createStatesArray(\stdClass $rule): array {
    $this->statesBuilder->clear();
    $this->addStates(
      $rule->effect,
      $this->getFieldName($rule->condition->scope),
      $rule->condition->schema,
    );

    return $this->statesBuilder->toArray();
  }

  private function getFieldName(string $scope): string {
    return FormPropertyUtil::getFormNameForPropertyPath(ScopePointer::new($scope)->getPropertyPath());
  }

  private function addStates(string $effect, string $fieldName, \stdClass $schema, bool $negate = FALSE): void {
    if (property_exists($schema, 'not')) {
      $this->addStates($effect, $fieldName, $schema->not, !$negate);
    }

    if (property_exists($schema, 'const')) {
      $this->statesBuilder->add($effect, $fieldName, $schema->const, $negate);
    }

    if (property_exists($schema, 'enum')) {
      $this->statesBuilder->add($effect, $fieldName, $schema->enum, $negate);
    }

    if (property_exists($schema, 'properties')) {
      foreach ($schema->properties as $property => $propertySchema) {
        $propertyFieldName = $fieldName . '[' . $property . ']';
        $this->addStates($effect, $propertyFieldName, $propertySchema, $negate);
      }
    }
  }

}
