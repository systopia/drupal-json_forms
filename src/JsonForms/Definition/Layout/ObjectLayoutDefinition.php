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

use Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition;
use Drupal\json_forms\JsonForms\Definition\Control\ObjectControlDefinition;
use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;

/**
 * Used when a control references an object.
 */
final class ObjectLayoutDefinition extends LayoutDefinition {

  public static function fromDefinition(ObjectControlDefinition $definition): self {
    $layoutSchema = (object) [
      'type' => 'VerticalLayout',
      'elements' => [],
    ];

    // @phpstan-ignore foreach.nonIterable
    foreach ($definition->getProperties() as $propertyName => $propertySchema) {
      $layoutSchema->elements[] = (object) [
        'type' => 'Control',
        'scope' => $definition->getScope() . '/properties/' . $propertyName,
      ];
    }

    $layoutDefinition = new self(
      $layoutSchema,
      $definition->getPropertySchema(),
      $definition->isUiReadonly(),
      $definition->getRootDefinition()
    );

    if (NULL !== $definition->getScopePrefix()) {
      $layoutDefinition = $layoutDefinition->withScopePrefix($definition->getScopePrefix());
    }

    return $layoutDefinition;
  }

  protected function createElement(\stdClass $element, \stdClass $objectSchema): DefinitionInterface {
    return ControlDefinition::fromObjectSchema(
      $element,
      $objectSchema,
      $this->isReadonly(),
      $this->getRootDefinition()
    );
  }

}
