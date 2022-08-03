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

use Assert\Assertion;
use Drupal\Core\Form\FormStateInterface;
use Drupal\json_forms\Form\Util\FormValueAccessor;
use Opis\JsonSchema\JsonPointer;

final class FormValidationMapper implements FormValidationMapperInterface {

  public function mapData(ValidationResult $validationResult, FormStateInterface $formState): void {
    $formState->setValues($validationResult->getData());
    $form = &$formState->getCompleteForm();
    FormValueAccessor::setValue($form, [], $validationResult->getData());
  }

  public function mapErrors(ValidationResult $validationResult, FormStateInterface $formState): void {
    foreach ($validationResult->getLeafErrorMessages() as $pointer => $messages) {
      $pointer = JsonPointer::parse($pointer);
      Assertion::notNull($pointer);
      $element = ['#parents' => $pointer->absolutePath()];
      $formState->setError($element, implode("\n", $messages));
    }
  }

}
