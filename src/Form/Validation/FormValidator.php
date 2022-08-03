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

use Assert\Assertion;
use Drupal\Core\Form\FormStateInterface;
use Drupal\json_forms\Form\Util\JsonConverter;
use Opis\JsonSchema\Validator as OpisValidator;
use Systopia\JsonSchema\Errors\ErrorCollector;

final class FormValidator implements FormValidatorInterface {

  private OpisValidator $validator;

  public function __construct(OpisValidator $validator) {
    $this->validator = $validator;
  }

  /**
   * @throws \JsonException
   */
  public function validate(FormStateInterface $formState): ValidationResult {

    $data = JsonConverter::toStdClass($formState->getValues());
    $jsonSchema = $formState->get('jsonSchema');
    Assertion::isInstanceOf($jsonSchema, \stdClass::class);
    $errorCollector = new ErrorCollector();
    $this->validator->validate($data, $jsonSchema, ['errorCollector' => $errorCollector]);

    return new ValidationResult(JsonConverter::toArray($data), $errorCollector);
  }

}
