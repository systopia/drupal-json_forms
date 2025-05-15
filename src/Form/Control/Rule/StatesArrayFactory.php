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

use Drupal\json_forms\JsonForms\Definition\Control\ObjectControlDefinition;
use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;
use Drupal\json_forms\JsonForms\Definition\Layout\LayoutDefinition;

/**
 * This class tries to create a Drupal states array from a JSON Forms rule.
 * Though, not everything in a condition schema can be mapped to Drupal
 * functionality.
 *
 * Note: In JSON Forms controls inside of arrays cannot reference properties
 * outside of that array.
 * See: https://github.com/eclipsesource/jsonforms/issues/2094.
 *
 * @see https://jsonforms.io/docs/uischema/rules
 * @see https://www.drupal.org/docs/drupal-apis/form-api/conditional-form-fields
 */
final class StatesArrayFactory implements StatesArrayFactoryInterface {

  private StatesBuilder $statesBuilder;

  public function __construct() {
    $this->statesBuilder = new StatesBuilder();
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  public function createStatesArray(DefinitionInterface $definition): array {
    $rule = $definition->getRule();
    if (NULL === $rule) {
      return [];
    }

    $rootDefinition = $definition->getRootDefinition();
    if ($rootDefinition instanceof ObjectControlDefinition) {
      $rootDefinition = $rootDefinition->getLayoutDefinition();
    }
    elseif (!$rootDefinition instanceof LayoutDefinition) {
      return [];
    }

    $this->statesBuilder->clear();
    $this->addStates(
      $rule->effect,
      $rootDefinition,
      $rule->condition->scope,
      $rule->condition->schema
    );

    return $this->statesBuilder->toArray();
  }

  private function addStates(
    string $effect,
    LayoutDefinition $rootDefinition,
    string $scope,
    \stdClass $conditionSchema,
    bool $negate = FALSE,
    bool $isContains = FALSE
  ): void {
    if (property_exists($conditionSchema, 'const')) {
      $controlDefinition = $rootDefinition->findControlDefinition($scope);
      if (NULL !== $controlDefinition) {
        $this->statesBuilder->add($effect, $controlDefinition, $conditionSchema->const, $negate, $isContains);
      }
    }

    if (property_exists($conditionSchema, 'enum')) {
      $controlDefinition = $rootDefinition->findControlDefinition($scope);
      if (NULL !== $controlDefinition) {
        $this->statesBuilder->add($effect, $controlDefinition, $conditionSchema->enum, $negate, $isContains);
      }
    }

    if (property_exists($conditionSchema, 'not')) {
      $this->addStates($effect, $rootDefinition, $scope, $conditionSchema->not, !$negate, $isContains);
    }

    if (property_exists($conditionSchema, 'contains')) {
      $this->addStates($effect, $rootDefinition, $scope, $conditionSchema->contains, $negate, TRUE);
    }

    if (property_exists($conditionSchema, 'properties')) {
      foreach ($conditionSchema->properties as $property => $propertyConditionSchema) {
        $this->addStates(
          $effect,
          $rootDefinition,
          "$scope/properties/$property",
          $propertyConditionSchema,
          $negate,
          $isContains
        );
      }
    }
  }

}
