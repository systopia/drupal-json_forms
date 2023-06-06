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

use Assert\Assertion;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\json_forms\Form\Util\FieldNameUtil;

final class RecalculateCallback {

  /**
   * @phpstan-param array<int|string, mixed> $form $form
   */
  public static function onChange(array &$form, FormStateInterface $formState): AjaxResponse {
    /** @var \Drupal\json_forms\Form\AbstractJsonFormsForm $formObject */
    $formObject = $formState->getFormObject();
    $data = FieldNameUtil::toFormData($formObject->calculateData($formState));

    $response = new AjaxResponse();
    static::addInvokeCommands($response, $formState, $data);

    return $response;
  }

  /**
   * @phpstan-param array<int|string, mixed> $newData
   */
  private static function addInvokeCommands(AjaxResponse $response,
    FormStateInterface $formState,
    array $newData
  ): void {
    $oldData = $formState->getValues();
    foreach (self::arrayMergeUniqueKeys($oldData, $newData) as $key) {
      $oldValue = $oldData[$key] ?? NULL;
      $newValue = $newData[$key] ?? NULL;
      static::doAddInvokeCommands($response, $formState, $oldValue, $newValue, (string) $key);
    }
  }

  /**
   * @param mixed $oldData
   * @param mixed $newData
   */
  private static function doAddInvokeCommands(AjaxResponse $response,
    FormStateInterface $formState,
    $oldData,
    $newData,
    string $name
  ): void {
    if ($oldData === $newData
      || NULL === $newData && '' === $oldData
      || \is_numeric($newData) && \is_numeric($oldData) && (float) $newData === (float) $oldData
    ) {
      return;
    }

    if (\is_array($oldData) || \is_array($newData)) {
      if (!\is_array($newData)) {
        $newData = [];
      }
      if (!\is_array($oldData)) {
        $oldData = [];
      }
      foreach (self::arrayMergeUniqueKeys($oldData, $newData) as $key) {
        $oldValue = $oldData[$key] ?? NULL;
        $newValue = $newData[$key] ?? NULL;
        static::doAddInvokeCommands($response, $formState, $oldValue, $newValue, $name . '[' . $key . ']');
      }
    }
    else {
      /** @phpstan-var array<int|string, mixed>|null $form */
      $form = $formState->get(['form', $name]);
      if (NULL !== $form) {
        /** @var string $type */
        $type = $form['#type'];
        $response->addCommand(static::createCommand($type, $name, $newData));
      }
    }

  }

  /**
   * @phpstan-param array<int|string, mixed> $array1
   * @phpstan-param array<int|string, mixed> $array2
   *
   * @phpstan-return array<int|string>
   */
  private static function arrayMergeUniqueKeys(array $array1, array $array2): array {
    return \array_unique(\array_merge(\array_keys($array1), \array_keys($array2)));
  }

  /**
   * @param mixed $value
   */
  private static function createCommand(string $type, string $name, $value): InvokeCommand {
    $selector = sprintf('[name="%s"]', $name);
    if ('checkbox' === $type) {
      $checked = TRUE === $value;
      return new InvokeCommand($selector, 'prop', ['checked', $checked]);
    }

    if ('radio' === $type) {
      if (NULL === $value) {
        return new InvokeCommand($selector, 'prop', ['checked', FALSE]);
      }

      Assertion::scalar($value);
      $selector .= sprintf('[value="%s"]', $value);
      return new InvokeCommand($selector, 'prop', ['checked', TRUE]);
    }

    return new InvokeCommand($selector, 'val', [$value]);
  }

}
