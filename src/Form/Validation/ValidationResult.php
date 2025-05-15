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

namespace Drupal\json_forms\Form\Validation;

use Opis\JsonSchema\Errors\ValidationError;
use Systopia\JsonSchema\Errors\ErrorCollector;
use Systopia\JsonSchema\Translation\ErrorTranslator;
use Systopia\JsonSchema\Translation\NullTranslator;
use Systopia\JsonSchema\Translation\TranslatorInterface;

final class ValidationResult {

  /**
   * @var array<int|string, mixed>
   */
  private array $data;

  private ErrorCollector $errorCollector;

  private ErrorTranslator $errorTranslator;

  /**
   * @param array<int|string, mixed> $data
   * @param \Systopia\JsonSchema\Errors\ErrorCollector $errorCollector
   */
  public function __construct(array $data, ErrorCollector $errorCollector, ?TranslatorInterface $translator = NULL) {
    $this->data = $data;
    $this->errorCollector = $errorCollector;
    $this->errorTranslator = new ErrorTranslator($translator ?? new NullTranslator());
  }

  /**
   * @return array<int|string, mixed>
   */
  public function getData(): array {
    return $this->data;
  }

  /**
   * @return array<string, non-empty-array<string>>
   */
  public function getErrorMessages(): array {
    return $this->mapErrorsToMessages($this->errorCollector->getErrors());
  }

  /**
   * @return array<string, non-empty-array<string>>
   */
  public function getLeafErrorMessages(): array {
    return $this->mapErrorsToMessages($this->errorCollector->getLeafErrors());
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
  private function mapErrorsToMessages(array $errors): array {
    return array_map(
      fn (array $errors): array => array_map(
        fn (ValidationError $error): string => $this->errorTranslator->trans($error),
        $errors
      ), $errors
    );
  }

}
