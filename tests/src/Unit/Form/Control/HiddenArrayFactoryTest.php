<?php

/*
 * Copyright (C) 2022 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.
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
use Drupal\json_forms\Form\Control\HiddenArrayFactory;
use Drupal\json_forms\Form\FormArrayFactoryInterface;
use Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\json_forms\Form\Control\HiddenArrayFactory
 */
final class HiddenArrayFactoryTest extends TestCase {

  private HiddenArrayFactory $factory;

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject&\Drupal\json_forms\Form\FormArrayFactoryInterface
   */
  private MockObject $formArrayFactoryMock;

  private FormState $formState;

  protected function setUp(): void {
    parent::setUp();
    $this->factory = new HiddenArrayFactory();
    $this->formArrayFactoryMock = $this->createMock(FormArrayFactoryInterface::class);
    $this->formState = new FormState();
  }

  public function testCreateFormArray(): void {
    $jsonSchema = (object) [
      'type' => 'object',
      'properties' => (object) [
        'test' => (object) [
          'type' => 'object',
          'properties' => (object) [
            'foo' => (object) [
              'type' => 'string',
              'const' => 'test',
            ],
          ],
        ],
      ],
    ];

    $uiSchema = (object) [
      'type' => 'Control',
      'scope' => '#/properties/test/properties/foo',
      'options' => (object) [
        'type' => 'hidden',
      ],
    ];

    $definition = ControlDefinition::fromJsonSchema($uiSchema, $jsonSchema, FALSE);
    $form = $this->factory->createFormArray($definition, $this->formState, $this->formArrayFactoryMock);

    static::assertSame('hidden', $form['#type']);
    static::assertSame('test', $form['#value']);
    static::assertSame(['test', 'foo'], $form['#parents']);
    static::assertTrue($form['#tree']);
    static::assertSame('#/properties/test/properties/foo', $form['#_scope']);
  }

  public function testSupportsDefinition(): void {
    $jsonSchema = (object) [
      'type' => 'object',
      'properties' => (object) [
        'test' => (object) [
          'type' => 'string',
        ],
      ],
    ];

    $uiSchema = (object) [
      'type' => 'Control',
      'scope' => '#/properties/test',
      'options' => (object) ['type' => 'hidden'],
    ];

    $definition = ControlDefinition::fromJsonSchema($uiSchema, $jsonSchema, FALSE);
    static::assertTrue($this->factory->supportsDefinition($definition));

    $uiSchema->options->type = 'test';
    $definition = ControlDefinition::fromJsonSchema($uiSchema, $jsonSchema, FALSE);
    static::assertFalse($this->factory->supportsDefinition($definition));

    unset($uiSchema->options);
    $definition = ControlDefinition::fromJsonSchema($uiSchema, $jsonSchema, FALSE);
    static::assertFalse($this->factory->supportsDefinition($definition));
  }

}
