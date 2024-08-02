<?php

/*
 * Copyright (C) 2024 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
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

  private \stdClass $uiSchema;

  private \stdClass $jsonSchema;

  private bool $parentReadonly;

  public function __construct(\stdClass $uiSchema, \stdClass $jsonSchema, bool $parentReadonly) {
    $this->uiSchema = $uiSchema;
    $this->jsonSchema = $jsonSchema;
    $this->parentReadonly = $parentReadonly;
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
    return new static($this->uiSchema, $this->jsonSchema, $this->parentReadonly);
  }

}
