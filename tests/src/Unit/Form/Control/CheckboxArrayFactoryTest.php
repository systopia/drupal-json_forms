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
use Drupal\Tests\UnitTestCase;
use Drupal\json_forms\Form\Control\Callbacks\CheckboxValueCallback;
use Drupal\json_forms\Form\Control\CheckboxArrayFactory;
use Drupal\json_forms\Form\FormArrayFactoryInterface;
use Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition;
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

    $definition = ControlDefinition::fromJsonSchema($uiSchema, $jsonSchema, FALSE, NULL);
    $form = $this->factory->createFormArray($definition, $this->formState, $this->formArrayFactoryMock);

    $expected = [
      '#type' => 'checkbox',
      '#value_callback' => CheckboxValueCallback::class . '::convert',
      '#disabled' => FALSE,
      '#required' => FALSE,
      '#parents' => ['test', 'foo'],
      '#title' => 'Foo',
      '#tree' => TRUE,
      '#limit_validation_errors' => [],
      '#_scope' => '#/properties/test/properties/foo',
      '#_nullable' => FALSE,
    ];
    static::assertEquals($expected, $form);
  }

  public function testCreateFormArrayRequired(): void {
    $jsonSchema = (object) [
      'type' => 'object',
      'properties' => (object) [
        'test' => (object) [
          'type' => 'object',
          'properties' => (object) [
            'foo' => (object) [
              'type' => 'boolean',
              'description' => 'Test description',
              'const' => TRUE,
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

    $definition = ControlDefinition::fromJsonSchema($uiSchema, $jsonSchema, FALSE, NULL);
    $form = $this->factory->createFormArray($definition, $this->formState, $this->formArrayFactoryMock);

    $expected = [
      '#type' => 'checkbox',
      '#value_callback' => CheckboxValueCallback::class . '::convert',
      '#disabled' => TRUE,
      '#required' => TRUE,
      '#value' => TRUE,
      '#parents' => ['test', 'foo'],
      '#title' => 'Test',
      // phpcs:disable DrupalPractice.General.DescriptionT.DescriptionT
      '#description' => 'Test description',
      // phpcs:enable
      '#tree' => TRUE,
      '#limit_validation_errors' => [],
      '#_scope' => '#/properties/test/properties/foo',
      '#_nullable' => FALSE,
    ];
    static::assertEquals($expected, $form);
  }

  public function testCreateFormArrayRequiredDefaultFalse(): void {
    $jsonSchema = (object) [
      'type' => 'object',
      'properties' => (object) [
        'test' => (object) [
          'type' => 'object',
          'properties' => (object) [
            'foo' => (object) [
              'type' => 'boolean',
              'const' => TRUE,
              'default' => FALSE,
            ],
          ],
          'required' => ['foo'],
        ],
      ],
    ];

    $uiSchema = (object) [
      'type' => 'Control',
      'scope' => '#/properties/test/properties/foo',
    ];

    $definition = ControlDefinition::fromJsonSchema($uiSchema, $jsonSchema, FALSE, NULL);
    $form = $this->factory->createFormArray($definition, $this->formState, $this->formArrayFactoryMock);

    $expected = [
      '#type' => 'checkbox',
      '#value_callback' => CheckboxValueCallback::class . '::convert',
      '#disabled' => FALSE,
      '#required' => TRUE,
      '#default_value' => FALSE,
      '#parents' => ['test', 'foo'],
      '#title' => 'Foo',
      '#tree' => TRUE,
      '#limit_validation_errors' => [],
      '#_scope' => '#/properties/test/properties/foo',
      '#_nullable' => FALSE,
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

    $definition = ControlDefinition::fromJsonSchema($uiSchema, $jsonSchema, FALSE, NULL);
    static::assertTrue($this->factory->supportsDefinition($definition));

    $jsonSchema->properties->test->type = 'string';
    $definition = ControlDefinition::fromJsonSchema($uiSchema, $jsonSchema, FALSE, NULL);
    static::assertFalse($this->factory->supportsDefinition($definition));
  }

}
