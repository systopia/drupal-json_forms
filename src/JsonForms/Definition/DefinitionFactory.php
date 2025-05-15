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

use Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition;
use Drupal\json_forms\JsonForms\Definition\Custom\CustomDefinition;
use Drupal\json_forms\JsonForms\Definition\Custom\MarkupDefinition;
use Drupal\json_forms\JsonForms\Definition\Layout\LayoutDefinition;

final class DefinitionFactory {

  /**
   * @throws \InvalidArgumentException
   */
  public static function createDefinition(
    \stdClass $uiSchema,
    \stdClass $jsonSchema,
    bool $parentUiReadonly = FALSE,
    ?DefinitionInterface $rootDefinition = NULL,
  ): DefinitionInterface {
    if ('Control' === $uiSchema->type) {
      return ControlDefinition::fromJsonSchema($uiSchema, $jsonSchema, $parentUiReadonly, $rootDefinition);
    }

    if ('Markup' === $uiSchema->type) {
      return new MarkupDefinition($uiSchema, $rootDefinition);
    }

    if (property_exists($uiSchema, 'elements')) {
      return new LayoutDefinition($uiSchema, $jsonSchema, $parentUiReadonly, $rootDefinition);
    }

    return new CustomDefinition($uiSchema, $jsonSchema, $parentUiReadonly, $rootDefinition);
  }

  /**
   * @throws \InvalidArgumentException
   */
  public static function createChildDefinition(
    \stdClass $uiSchema,
    \stdClass $jsonSchema,
    bool $parentUiReadonly,
    DefinitionInterface $rootDefinition,
  ): DefinitionInterface {
    return self::createDefinition($uiSchema, $jsonSchema, $parentUiReadonly, $rootDefinition);
  }

}
