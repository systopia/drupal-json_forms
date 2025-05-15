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

namespace Drupal\json_forms\JsonForms\Definition\Custom;

use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;

/**
 * Custom JSONForms extension to display markup content.
 *
 * Example:
 * {
 *   "type": "Markup",
 *   "contentMediaType": "text/html",
 *   "content": "<em>example</em>"
 * }
 */
final class MarkupDefinition implements DefinitionInterface {

  private \stdClass $markupSchema;

  private DefinitionInterface $rootDefinition;

  public function __construct(\stdClass $markupSchema, ?DefinitionInterface $rootDefinition) {
    $this->markupSchema = $markupSchema;
    $this->rootDefinition = $rootDefinition ?? $this;
  }

  public function getContent(): string {
    return $this->markupSchema->content;
  }

  public function getContentMediaType(): string {
    return $this->markupSchema->contentMediaType;
  }

  /**
   * {@inheritDoc}
   */
  public function getKeywordValue(string $keyword, $default = NULL) {
    return $this->markupSchema->{$keyword} ?? $default;
  }

  public function getLabel(): ?string {
    return $this->markupSchema->label ?? NULL;
  }

  public function getMarkupSchema(): \stdClass {
    return $this->markupSchema;
  }

  public function getRootDefinition(): DefinitionInterface {
    return $this->rootDefinition;
  }

  public function getRule(): ?\stdClass {
    return $this->markupSchema->rule ?? NULL;
  }

  public function getType(): string {
    return $this->markupSchema->type;
  }

  /**
   * {@inheritDoc}
   */
  public function withScopePrefix(string $scopePrefix): DefinitionInterface {
    return new static($this->markupSchema, $this->rootDefinition);
  }

}
