<?php

/*
 * Copyright (C) 2022 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Drupal\json_forms\Form\Validation;

use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Errors\ValidationError;
use Systopia\JsonSchema\Errors\ErrorCollector;

final class ValidationResult {

  /**
   * @var array<string, mixed>
   */
  private array $data;

  private ErrorCollector $errorCollector;

  /**
   * @param array<string, mixed> $data
   * @param \Systopia\JsonSchema\Errors\ErrorCollector $errorCollector
   */
  public function __construct(array $data, ErrorCollector $errorCollector) {
    $this->data = $data;
    $this->errorCollector = $errorCollector;
  }

  /**
   * @return array<string, mixed>
   */
  public function getData(): array {
    return $this->data;
  }

  /**
   * @return array<string, non-empty-array<string>>
   */
  public function getErrorMessages(): array {
    return static::mapErrorsToMessages($this->errorCollector->getErrors());
  }

  /**
   * @return array<string, non-empty-array<string>>
   */
  public function getLeafErrorMessages(): array {
    return static::mapErrorsToMessages($this->errorCollector->getLeafErrors());
  }

  public function hasErrors(): bool {
    return $this->errorCollector->hasErrors();
  }

  public function isValid(): bool {
    return !$this->errorCollector->hasErrors();
  }

  /**
   * @param array<string, non-empty-array<ValidationError>> $errors
   *
   * @return array<string, non-empty-array<string>>
   */
  private static function mapErrorsToMessages(array $errors): array {
    $errorFormatter = static::getErrorFormatter();
    return array_map(
      fn (array $errors): array => array_map(
        fn (ValidationError $error): string => $errorFormatter->formatErrorMessage($error),
        $errors
      ), $errors
    );
  }

  private static function getErrorFormatter(): ErrorFormatter {
    static $errorFormatter;
    $errorFormatter ??= new ErrorFormatter();

    return $errorFormatter;
  }

}
