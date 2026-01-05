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

namespace Drupal\json_forms\JsonForms\Definition\Layout;

use Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition;
use Drupal\json_forms\JsonForms\Definition\Control\ObjectControlDefinition;
use Drupal\json_forms\JsonForms\Definition\DefinitionFactory;
use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;

class LayoutDefinition implements DefinitionInterface {

  private \stdClass $layoutSchema;

  /**
   * @var array<int, DefinitionInterface>
   */
  private array $elements = [];

  private bool $parentUiReadonly;

  private DefinitionInterface $rootDefinition;

  /**
   * @throws \InvalidArgumentException
   */
  public function __construct(
    \stdClass $layoutSchema,
    \stdClass $jsonSchema,
    bool $parentUiReadonly,
    ?DefinitionInterface $rootDefinition
  ) {
    $this->layoutSchema = $layoutSchema;
    $this->parentUiReadonly = $parentUiReadonly;
    $this->rootDefinition = $rootDefinition ?? $this;
    foreach ($this->layoutSchema->elements as $element) {
      $this->elements[] = $this->createElement($element, $jsonSchema);
    }
  }

  /**
   * @return array<int, DefinitionInterface>
   */
  public function getElements(): array {
    return $this->elements;
  }

  public function getLayoutSchema(): \stdClass {
    return $this->layoutSchema;
  }

  /**
   * {@inheritDoc}
   */
  public function getKeywordValue(string $keyword, $default = NULL) {
    return $this->layoutSchema->{$keyword} ?? $default;
  }

  public function getRootDefinition(): DefinitionInterface {
    return $this->rootDefinition;
  }

  public function getRule(): ?\stdClass {
    return $this->layoutSchema->rule ?? NULL;
  }

  public function getType(): string {
    return $this->layoutSchema->type;
  }

  public function isReadonly(): bool {
    return $this->layoutSchema->options->readonly ?? $this->parentUiReadonly;
  }

  public function getOptions(): ?\stdClass {
    return $this->layoutSchema->options ?? NULL;
  }

  public function getOptionsValue(string $key, mixed $default = NULL): mixed {
    return $this->layoutSchema->options->{$key} ?? $default;
  }

  public function findControlDefinition(string $scope): ?ControlDefinition {
    foreach ($this->elements as $element) {
      if ($element instanceof ControlDefinition) {
        if ($element->getScope() === $scope) {
          return $element;
        }

        if ($element instanceof ObjectControlDefinition && str_starts_with($scope, $element->getScope() . '/')) {
          return $element->getLayoutDefinition()->findControlDefinition($scope);
        }
      }
      elseif ($element instanceof LayoutDefinition) {
        if (NULL !== ($controlDefinition = $element->findControlDefinition($scope))) {
          return $controlDefinition;
        }
      }
    }

    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function withScopePrefix(string $scopePrefix): DefinitionInterface {
    $definition = clone $this;
    $definition->elements = [];

    foreach ($this->elements as $element) {
      $definition->elements[] = $element->withScopePrefix($scopePrefix);
    }

    return $definition;
  }

  /**
   * @throws \InvalidArgumentException
   */
  protected function createElement(\stdClass $element, \stdClass $jsonSchema): DefinitionInterface {
    return DefinitionFactory::createChildDefinition($element, $jsonSchema, $this->isReadonly(), $this->rootDefinition);
  }

}
