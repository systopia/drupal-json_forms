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

namespace Drupal\json_forms_example\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\json_forms_example\Form\JsonFormsExampleForm;

final class ExampleController extends ControllerBase {

  /**
   * @return array<int|string, mixed>
   */
  public function example(): array {
    $jsonSchema = (object) [
      'type' => 'object',
      'properties' => (object) [
        'test' => (object) [
          'type' => 'object',
          'properties' => (object) [
            'foo' => (object) [
              'type' => 'string',
            ],
            'bar' => (object) [
              'type' => 'number',
              'minimum' => 10,
            ],
            'baz' => (object) [
              'type' => 'array',
              'items' => (object) [
                'type' => 'string',
              ],
            ],
            'action' => (object) ['type' => 'string'],
          ],
        ],
      ],
    ];

    $uiSchema = (object) [
      'type' => 'Group',
      'label' => 'Example form',
      'elements' => [
        (object) [
          'type' => 'Group',
          'label' => 'Foo and bar',
          'description' => 'Lorem ipsum',
          'elements' => [
            (object) [
              'type' => 'Control',
              'scope' => '#/properties/test/properties/foo',
              'label' => 'FOO',
            ],
            (object) [
              'type' => 'Control',
              'scope' => '#/properties/test/properties/bar',
            ],
          ],
        ],
        (object) [
          'type' => 'Control',
          'scope' => '#/properties/test/properties/baz',
        ],
        (object) [
          'type' => 'Control',
          'scope' => '#/properties/test/properties/action',
          'label' => 'Submit',
          'options' => (object) [
            'type' => 'submit',
            'data' => 'test',
          ],
        ],
      ],
    ];

    return $this->formBuilder()->getForm(JsonFormsExampleForm::class, $jsonSchema, $uiSchema);
  }

}
