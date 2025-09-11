<?php

/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Drupal\json_forms\Form\Control\Callbacks;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Form\FormStateInterface;
use Drupal\json_forms\Form\Control\Callbacks\Util\RecalculateCallbackUtil;
use Drupal\json_forms\Form\Util\FieldNameUtil;

final class RecalculateCallback {

  /**
   * This method may be used by other modules to have custom AJAX callbacks that
   * sill can perform recalculation.
   */
  public static function addAjaxCommands(AjaxResponse $response, FormStateInterface $formState): void {
    /** @var \Drupal\json_forms\Form\AbstractJsonFormsForm $formObject */
    $formObject = $formState->getFormObject();
    $newData = FieldNameUtil::toFormData($formObject->calculateData($formState));
    $oldData = $formState->getValues();
    RecalculateCallbackUtil::addAjaxCommands($response, $formState, $oldData, $newData);
  }

  /**
   * @param array<int|string, mixed> $element
   *
   * @return array<int|string, mixed>
   */
  public static function processElement(array $element, FormStateInterface $formState): array {
    if (TRUE !== $formState->get('$calculateUsed')) {
      if (is_array($element['#ajax'] ?? NULL) && [static::class, 'onChange'] === $element['#ajax']['callback']) {
        unset($element['#ajax']);
      }
    }

    return $element;
  }

  /**
   * @phpstan-param array<int|string, mixed> $form $form
   */
  public static function onChange(array &$form, FormStateInterface $formState): AjaxResponse {
    $response = new AjaxResponse();
    self::addAjaxCommands($response, $formState);

    return $response;
  }

}
