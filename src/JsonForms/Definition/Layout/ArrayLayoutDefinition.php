<?php

/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

use Drupal\json_forms\JsonForms\Definition\DefinitionFactory;
use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;

/**
 * This is used in controls that scope references an array. It differs from the
 * normal LayoutDefinition that it uses itself as root definition instead of its
 * own root definition.
 */
final class ArrayLayoutDefinition extends LayoutDefinition {

  protected function createElement(\stdClass $element, \stdClass $jsonSchema): DefinitionInterface {
    return DefinitionFactory::createChildDefinition($element, $jsonSchema, $this->isReadonly(), $this);
  }

}
