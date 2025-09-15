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

namespace Drupal\json_forms\Form\Control\Callbacks;

use Drupal\Core\Form\FormStateInterface;

final class TriggeringElementCallback {

  /**
   * @param array<int|string, mixed> $element
   *
   * @return array<int|string, mixed>
   */
  public static function onAfterBuild(array $element, FormStateInterface $formState): array {
    // For disabled elements the triggering element is not set. (Required if
    // disabled element is used for the initial calculation call.)
    if (NULL === $formState->getTriggeringElement() && self::elementTriggeredScriptedSubmission($element, $formState)) {
      $formState->setTriggeringElement($element);
    }

    return $element;
  }

  /**
   * Based on
   * \Drupal\Core\Form\FormBuilder::elementTriggeredScriptedSubmission().
   *
   * Detects if an element triggered the form submission via Ajax.
   *
   * This detects button or non-button controls that trigger a form submission
   * via Ajax or some other scriptable environment. These environments can set
   * the special input key '_triggering_element_name' to identify the triggering
   * element. If the name alone doesn't identify the element uniquely, the input
   * key '_triggering_element_value' may also be set to require a match on
   * element value. An example where this is needed is if there are several
   * // buttons all named 'op', and only differing in their value.
   *
   * @param array<int|string, mixed> $element
   */
  private static function elementTriggeredScriptedSubmission(array $element, FormStateInterface $formState): bool {
    $input = $formState->getUserInput();
    if ($element['#name'] === ($input['_triggering_element_name'] ?? NULL)) {
      // @phpstan-ignore equal.notAllowed
      if (!isset($input['_triggering_element_value']) || $input['_triggering_element_value'] == $element['#value']) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
