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

namespace Drupal\json_forms_example\Form;

use Assert\Assertion;
use Drupal\Core\Form\FormStateInterface;
use Drupal\json_forms\Form\AbstractJsonFormsForm;

final class JsonFormsExampleForm extends AbstractJsonFormsForm {

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return 'json_forms_example';
  }

  /**
   * {@inheritDoc}
   *
   * @param array<int|string, mixed> $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Note: Using underscore case is enforced by Drupal's argument resolver.
   * @param \stdClass $jsonSchema
   * @param \stdClass $uiSchema
   * @param int $flags
   *
   * @return array<int|string, mixed>
   *
   * @throws \InvalidArgumentException
   */
  public function buildForm(
    array $form,
    FormStateInterface $form_state,
    ?\stdClass $jsonSchema = NULL,
    ?\stdClass $uiSchema = NULL,
    int $flags = 0
  ): array {
    Assertion::notNull($jsonSchema);
    Assertion::notNull($uiSchema);

    return $this->buildJsonFormsForm($form, $form_state, $jsonSchema, $uiSchema, $flags);
  }

  /**
   * {@inheritDoc}
   *
   * @param array<int|string, mixed> $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  public function submitForm(array &$form, FormStateInterface $formState): void {
    $data = $this->getSubmittedData($formState);
    $this->messenger()->addMessage($this->t(
      'Submitted data: <pre>%data%</pre>',
      ['%data%' => var_export($data, TRUE)]
    ));
  }

}
