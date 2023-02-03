<?php

/*
 * Copyright (C) 2023 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.
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

namespace Drupal\json_forms\Form\Util;

use Drupal\Core\Form\FormStateInterface;

final class FormCallbackExecutor {

  public static function executePreSchemaValidationCallbacks(FormStateInterface $formState): void {
    self::executeCallbacks(FormCallbackRegistrator::PRE_SCHEMA_VALIDATION_CALLBACKS_PROPERTY_KEY, $formState);
  }

  private static function executeCallbacks(string $propertyKey, FormStateInterface $formState): void {
    /** @phpstan-var array<string, array{callable, array<mixed>}> $callbackDefinitions */
    $callbackDefinitions = $formState->get($propertyKey) ?? [];
    foreach ($callbackDefinitions as $key => $callbackDefinition) {
      $callback = $callbackDefinition[0];
      $args = array_merge([$formState, $key], $callbackDefinition[1]);
      call_user_func_array($callback, $args);
    }
  }

}
