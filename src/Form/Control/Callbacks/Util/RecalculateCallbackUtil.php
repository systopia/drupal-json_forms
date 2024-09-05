<?php

/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Drupal\json_forms\Form\Control\Callbacks\Util;

use Assert\Assertion;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormStateInterface;

final class RecalculateCallbackUtil {

  /**
   * @phpstan-param array<int|string, mixed> $oldData
   * @phpstan-param array<int|string, mixed> $newData
   * @phpstan-param list<int|string>|null $keysToConsider
   *   If NULL, the keys of both $oldData and $newData are considered.
   * @param bool $strictBooleanComparison
   *   If FALSE and the new value is a boolean, the comparison with the old
   *   value is not strict, e.g. '==' instead '==='. This can be used when the
   *   old data comes directly from form input.
   */
  public static function addAjaxCommands(
    AjaxResponse $response,
    FormStateInterface $formState,
    array $oldData,
    array $newData,
    ?array $keysToConsider = NULL,
    bool $strictBooleanComparison = TRUE
  ): void {
    $keysToConsider ??= self::arrayMergeUniqueKeys($oldData, $newData);
    foreach ($keysToConsider as $key) {
      $oldValue = $oldData[$key] ?? NULL;
      $newValue = $newData[$key] ?? NULL;
      static::doAddAjaxCommands($response, $formState, $oldValue, $newValue, (string) $key, $strictBooleanComparison);
    }
  }

  /**
   * @param mixed $oldData
   * @param mixed $newData
   */
  private static function doAddAjaxCommands(
    AjaxResponse $response,
    FormStateInterface $formState,
    $oldData,
    $newData,
    string $name,
    bool $strictBooleanComparison
  ): void {
    if ($oldData === $newData
      || NULL === $newData && '' === $oldData
      || \is_numeric($newData) && \is_numeric($oldData) && (float) $newData === (float) $oldData
      || (is_bool($newData) && !$strictBooleanComparison && $newData == $oldData)
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
        static::doAddAjaxCommands(
          $response,
          $formState,
          $oldValue,
          $newValue,
          $name . '[' . $key . ']',
          $strictBooleanComparison
        );
      }
    }
    else {
      /** @phpstan-var array<int|string, mixed>|null $form */
      $form = $formState->get(['form', $name]);
      if (NULL !== $form && 'value' !== $form['#type']) {
        /** @var string $type */
        $type = $form['#type'];
        $response->addCommand(static::createAjaxCommand($type, $name, $newData));
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
  private static function createAjaxCommand(string $type, string $name, $value): InvokeCommand {
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
