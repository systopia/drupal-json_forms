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

namespace Drupal\json_forms\JsonForms\Definition\Control;

use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;
use Drupal\json_forms\JsonForms\Definition\Layout\ObjectLayoutDefinition;
use Drupal\json_forms\Util\ConvertUtil;

final class ObjectControlDefinition extends ControlDefinition {

  private ObjectLayoutDefinition $layoutDefinition;

  public function __construct(
    \stdClass $controlSchema,
    \stdClass $objectSchema,
    bool $parentUiReadonly,
    ?DefinitionInterface $rootDefinition,
    ?string $scopePrefix = NULL
  ) {
    parent::__construct($controlSchema, $objectSchema, $parentUiReadonly, $rootDefinition, $scopePrefix);
    $this->layoutDefinition = ObjectLayoutDefinition::fromDefinition($this);
  }

  public function getMaxProperties(): ?int {
    return ConvertUtil::stdClassToNull($this->propertySchema->maxProperties ?? NULL);
  }

  public function getMinProperties(): ?int {
    return ConvertUtil::stdClassToNull($this->propertySchema->minProperties ?? NULL);
  }

  /**
   * @return \stdClass
   */
  public function getProperties(): \stdClass {
    return $this->propertySchema->properties;
  }

  /**
   * @return array<string>|null
   */
  public function getRequired(): ?array {
    return ConvertUtil::stdClassToNull($this->propertySchema->required ?? NULL);
  }

  public function getDependentRequired(): ?\stdClass {
    return $this->propertySchema->dependentRequired ?? NULL;
  }

  /**
   * A layout definition containing controls for each object property.
   */
  public function getLayoutDefinition(): ObjectLayoutDefinition {
    return $this->layoutDefinition;
  }

}
