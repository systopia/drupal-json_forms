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

final class FormCallbackRegistrator {

  public const PRE_SCHEMA_VALIDATION_CALLBACKS_PROPERTY_KEY = 'json_forms.pre_schema_validation_callbacks';

  public static function clearPreSchemaValidationCallbacks(FormStateInterface $formState): void {
    $formState->set(self::PRE_SCHEMA_VALIDATION_CALLBACKS_PROPERTY_KEY, []);
  }

  /**
   * Registers a callback that is executed before JSON schema validation.
   *
   * Drupal's validation is finished in that case. It might be used to modify
   * the form values.
   *
   * @param string $key
   *   If there's already a callback registered with this key it will be
   *   replaced.
   * @param mixed ...$args
   *
   * @phpstan-param callable-string|array{class-string, string} $callback
   *   Callback function with the following signature:
   *   function(FormStateInterface $formState, string $key, mixed ...$args)
   *
   * @phpstan-ignore-next-line phpstan says type hint of $callback does not match.
   */
  public static function registerPreSchemaValidationCallback(
    FormStateInterface $formState,
    string $key,
    callable $callback,
    ...$args
  ): void {
    $formState->set(
      [self::PRE_SCHEMA_VALIDATION_CALLBACKS_PROPERTY_KEY, $key],
      [$callback, $args]
    );
  }

}
