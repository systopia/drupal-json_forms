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

namespace Drupal\json_forms\Form\Util;

use Drupal\Core\Form\FormStateInterface;

final class FormValidationUtil {

  /**
   * Allows to use a different form element to add a validation error than the
   * JSON schema validation would use. Useful when a Drupal form element makes
   * use of sub-elements, e.g. "text_format" uses the sub-elements "value" and
   * "format" where "value" contains the value validated by the JSON schema. So
   * validation errors detected by the JSON schema validator should be added to
   * the "value" element.
   *
   * @param list<int|string> $key
   *   Value of the "#parents" attribute.
   * @param list<int|string> $targetKey
   *   Value of the "#parents" attribute an error should be added to.
   */
  public static function addFormErrorMapping(FormStateInterface $formState, array $key, array $targetKey): void {
    $formState->set(['formErrorMappings', implode('/', $key)], $targetKey);
  }

  /**
   * @param list<int|string> $key
   *
   * @return list<int|string>
   *   The element key ("#parents" attribute) an error for the element with the
   *   given key should be added to. If no mapping is defined, the given key is
   *   returned.
   */
  public static function getFormErrorMapping(FormStateInterface $formState, array $key): array {
    // @phpstan-ignore return.type
    return $formState->get(['formErrorMappings', implode('/', $key)]) ?? $key;
  }

  /**
   * Allows to keep Drupal validation errors when the "$limitValidation" keyword
   * is used. Normally, all Drupal validation errors are cleared when
   * "$limitValidation" is used. This can be used to keep errors for fields that
   * cannot be validated by the JSON schema, e.g. the "format" sub-element of a
   * "text_format" element.
   *
   * @param list<int|string> $key
   *   The form element key ("#parents" attribute) for which to keep Drupal
   *   validation errors.
   */
  public static function addKeepFormErrorElementKey(FormStateInterface $formState, array $key): void {
    $formState->set(['keepFormErrorElementKeys', implode('/', $key)], $key);
  }

  /**
   * @return list<list<int|string>>
   *   List of element keys ("#parents" attribute) for which to keep Drupal
   *   validation errors.
   */
  public static function getKeepFormErrorElementKeys(FormStateInterface $formState): array {
    // @phpstan-ignore argument.type, return.type
    return array_values($formState->get('keepFormErrorElementKeys') ?? []);
  }

}
