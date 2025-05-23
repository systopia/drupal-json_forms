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

use Drupal\json_forms\Form\Util\JsonConverter;
use Opis\JsonSchema\Validator as OpisValidator;
use Systopia\JsonSchema\Errors\ErrorCollector;
use Systopia\JsonSchema\Translation\TranslatorInterface;

final class FormValidator implements FormValidatorInterface {

  private TranslatorInterface $translator;

  private OpisValidator $validator;

  public function __construct(TranslatorInterface $translator, OpisValidator $validator) {
    $this->translator = $translator;
    $this->validator = $validator;
  }

  /**
   * @throws \JsonException
   */
  public function validate(\stdClass $jsonSchema, array $data): ValidationResult {
    $data = JsonConverter::toStdClass($data);
    $errorCollector = new ErrorCollector();
    $this->validator->validate($data, $jsonSchema, ['errorCollector' => $errorCollector]);

    return new ValidationResult(JsonConverter::toArray($data), $errorCollector, $this->translator);
  }

}
