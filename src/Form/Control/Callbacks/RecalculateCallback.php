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
   * @phpstan-param array<int|string, mixed> $form $form
   */
  public static function onChange(array &$form, FormStateInterface $formState): AjaxResponse {
    /** @var \Drupal\json_forms\Form\AbstractJsonFormsForm $formObject */
    $formObject = $formState->getFormObject();
    $newData = FieldNameUtil::toFormData($formObject->calculateData($formState));
    $oldData = $formState->getValues();

    $response = new AjaxResponse();
    RecalculateCallbackUtil::addAjaxCommands($response, $formState, $oldData, $newData);

    return $response;
  }

}
