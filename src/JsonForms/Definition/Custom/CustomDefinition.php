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

namespace Drupal\json_forms\JsonForms\Definition\Custom;

use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;

/**
 * Used for unknown UI schema types.
 *
 * @codeCoverageIgnore
 */
final class CustomDefinition implements DefinitionInterface {

  private DefinitionInterface $rootDefinition;

  private \stdClass $uiSchema;

  private \stdClass $jsonSchema;

  private bool $parentReadonly;

  public function __construct(
    \stdClass $uiSchema,
    \stdClass $jsonSchema,
    bool $parentReadonly,
    ?DefinitionInterface $rootDefinition
  ) {
    $this->uiSchema = $uiSchema;
    $this->jsonSchema = $jsonSchema;
    $this->parentReadonly = $parentReadonly;
    $this->rootDefinition = $rootDefinition ?? $this;
  }

  public function getOptions(): ?\stdClass {
    return $this->uiSchema->options ?? NULL;
  }

  public function getOptionsValue(string $key, mixed $default = NULL): mixed {
    // @phpstan-ignore property.dynamicName
    return $this->uiSchema->options->{$key} ?? $default;
  }

  /**
   * {@inheritDoc}
   */
  public function getKeywordValue(string $keyword, $default = NULL) {
    return $this->uiSchema->{$keyword} ?? $default;
  }

  public function getJsonSchema(): \stdClass {
    return $this->jsonSchema;
  }

  public function getUiSchema(): \stdClass {
    return $this->uiSchema;
  }

  public function getRootDefinition(): DefinitionInterface {
    return $this->rootDefinition;
  }

  public function getRule(): ?\stdClass {
    return $this->uiSchema->rule ?? NULL;
  }

  public function getType(): string {
    return $this->uiSchema->type;
  }

  public function isParentReadonly(): bool {
    return $this->parentReadonly;
  }

  /**
   * {@inheritDoc}
   */
  public function withScopePrefix(string $scopePrefix): DefinitionInterface {
    return new static($this->uiSchema, $this->jsonSchema, $this->parentReadonly, $this->rootDefinition);
  }

}
