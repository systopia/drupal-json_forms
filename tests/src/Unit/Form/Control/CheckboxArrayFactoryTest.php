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

namespace Drupal\Tests\json_forms\Unit\Form\Control;

use Drupal\Core\Form\FormState;
use Drupal\json_forms\Form\Control\CheckboxArrayFactory;
use Drupal\json_forms\Form\FormArrayFactoryInterface;
use Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Drupal\json_forms\Form\Control\CheckboxArrayFactory
 * @covers \Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition
 * @group json_forms
 */
final class CheckboxArrayFactoryTest extends UnitTestCase {

  private CheckboxArrayFactory $factory;

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject&\Drupal\json_forms\Form\FormArrayFactoryInterface
   */
  private MockObject $formArrayFactoryMock;

  private FormState $formState;

  protected function setUp(): void {
    parent::setUp();
    $this->factory = new CheckboxArrayFactory();
    $this->formArrayFactoryMock = $this->createMock(FormArrayFactoryInterface::class);
    $this->formState = new FormState();
  }

  public function testCreateFormArraySimple(): void {
    $jsonSchema = (object) [
      'type' => 'object',
      'properties' => (object) [
        'test' => (object) [
          'type' => 'object',
          'properties' => (object) [
            'foo' => (object) [
              'type' => 'boolean',
            ],
          ],
        ],
      ],
    ];

    $uiSchema = (object) [
      'type' => 'Control',
      'scope' => '#/properties/test/properties/foo',
    ];

    $definition = ControlDefinition::fromJsonSchema($uiSchema, $jsonSchema);
    $form = $this->factory->createFormArray($definition, $this->formState, $this->formArrayFactoryMock);

    $expected = [
      '#type' => 'checkbox',
      '#default_value' => FALSE,
      '#return_value' => TRUE,
      '#disabled' => FALSE,
      '#required' => FALSE,
      '#parents' => ['test', 'foo'],
      '#title' => 'Foo',
      '#tree' => TRUE,
      '#limit_validation_errors' => [],
      '#_scope' => '#/properties/test/properties/foo',
    ];
    static::assertEquals($expected, $form);
  }

  public function testCreateFormArray(): void {
    $jsonSchema = (object) [
      'type' => 'object',
      'properties' => (object) [
        'test' => (object) [
          'type' => 'object',
          'properties' => (object) [
            'foo' => (object) [
              'type' => 'boolean',
              'description' => 'Test description',
            ],
          ],
          'required' => ['foo'],
        ],
      ],
    ];

    $uiSchema = (object) [
      'type' => 'Control',
      'scope' => '#/properties/test/properties/foo',
      'label' => 'Test',
      'options' => (object) [
        'readonly' => TRUE,
      ],
    ];

    $definition = ControlDefinition::fromJsonSchema($uiSchema, $jsonSchema);
    $form = $this->factory->createFormArray($definition, $this->formState, $this->formArrayFactoryMock);

    $expected = [
      '#type' => 'checkbox',
      '#default_value' => FALSE,
      '#return_value' => TRUE,
      '#disabled' => TRUE,
      '#required' => TRUE,
      '#parents' => ['test', 'foo'],
      '#title' => 'Test',
      // phpcs:disable DrupalPractice.General.DescriptionT.DescriptionT
      '#description' => 'Test description',
      // phpcs:enable
      '#tree' => TRUE,
      '#limit_validation_errors' => [],
      '#_scope' => '#/properties/test/properties/foo',
    ];
    static::assertEquals($expected, $form);
  }

  public function testSupportsDefinition(): void {
    $jsonSchema = (object) [
      'type' => 'object',
      'properties' => (object) [
        'test' => (object) [
          'type' => 'boolean',
        ],
      ],
    ];

    $uiSchema = (object) [
      'type' => 'Control',
      'scope' => '#/properties/test',
    ];

    $definition = ControlDefinition::fromJsonSchema($uiSchema, $jsonSchema);
    static::assertTrue($this->factory->supportsDefinition($definition));

    $jsonSchema->properties->test->type = 'string';
    $definition = ControlDefinition::fromJsonSchema($uiSchema, $jsonSchema);
    static::assertFalse($this->factory->supportsDefinition($definition));
  }

}
