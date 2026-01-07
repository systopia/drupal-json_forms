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

namespace Drupal\json_forms\JsonForms\Definition;

interface DefinitionInterface {

  public function getOptions(): ?\stdClass;

  public function getOptionsValue(string $key, mixed $default = NULL): mixed;

  /**
   * @param mixed $default
   *
   * @return mixed
   *   The value for the given keyword in the UI schema or $default if not set.
   */
  public function getKeywordValue(string $keyword, $default = NULL);

  public function getRootDefinition(): DefinitionInterface;

  public function getRule(): ?\stdClass;

  public function getType(): string;

  /**
   * @return static
   *   A new definition where the given scope prefix is prepended to the scopes
   *   of all Controls.
   */
  public function withScopePrefix(string $scopePrefix): self;

}
