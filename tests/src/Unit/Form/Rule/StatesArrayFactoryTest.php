<?php

/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Drupal\Tests\json_forms\Unit\Form\Rule;

use Drupal\json_forms\Form\Control\Rule\StatesArrayFactory;
use Drupal\json_forms\JsonForms\Definition\Layout\LayoutDefinition;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\json_forms\Form\Control\Rule\StatesArrayFactory
 * @covers \Drupal\json_forms\Form\Control\Rule\StatesBuilder
 */
final class StatesArrayFactoryTest extends TestCase {

  private StatesArrayFactory $factory;

  protected function setUp(): void {
    parent::setUp();
    $this->factory = new StatesArrayFactory();
  }

  public function testHide(): void {
    $jsonSchema = (object) [
      'type' => 'object',
      'properties' => (object) [
        'conditioned' => (object) [
          'type' => 'string',
        ],
        'foo' => (object) [
          'type' => 'object',
          'properties' => (object) [
            'bar' => (object) [
              'type' => 'string',
            ],
          ],
        ],
      ],
    ];

    $rule = (object) [
      'effect' => 'HIDE',
      'condition' => (object) [
        'scope' => '#/properties/foo/properties/bar',
        'schema' => (object) ['const' => 'bar'],
      ],
    ];
    $uiSchema = $this->createUiSchema($rule);

    $definition = new LayoutDefinition($uiSchema, $jsonSchema, FALSE, NULL);
    $conditionedDefinition = $definition->findControlDefinition('#/properties/conditioned');
    static::assertNotNull($conditionedDefinition);

    static::assertSame(
      [
        'invisible' => [
          '[name="foo[bar]"]' => [
            ['value' => 'bar'],
          ],
        ],
      ],
      $this->factory->createStatesArray($conditionedDefinition)
    );
  }

  public function testNotHide(): void {
    $jsonSchema = (object) [
      'type' => 'object',
      'properties' => (object) [
        'conditioned' => (object) [
          'type' => 'string',
        ],
        'foo' => (object) [
          'type' => 'object',
          'properties' => (object) [
            'bar' => (object) [
              'type' => 'string',
            ],
          ],
        ],
      ],
    ];

    $rule = (object) [
      'effect' => 'HIDE',
      'condition' => (object) [
        'scope' => '#/properties/foo/properties/bar',
        'schema' => (object) ['not' => (object) ['const' => 'bar']],
      ],
    ];
    $uiSchema = $this->createUiSchema($rule);

    $definition = new LayoutDefinition($uiSchema, $jsonSchema, FALSE, NULL);
    $conditionedDefinition = $definition->findControlDefinition('#/properties/conditioned');
    static::assertNotNull($conditionedDefinition);

    static::assertSame(
      [
        'visible' => [
          '[name="foo[bar]"]' => [
            ['value' => 'bar'],
          ],
        ],
      ],
      $this->factory->createStatesArray($conditionedDefinition)
    );
  }

  public function testShowEnum(): void {
    $jsonSchema = (object) [
      'type' => 'object',
      'properties' => (object) [
        'conditioned' => (object) [
          'type' => 'string',
        ],
        'foo' => (object) [
          'type' => 'object',
          'properties' => (object) [
            'bar' => (object) [
              'type' => 'string',
            ],
          ],
        ],
      ],
    ];

    $rule = (object) [
      'effect' => 'SHOW',
      'condition' => (object) [
        'scope' => '#/properties/foo/properties/bar',
        'schema' => (object) ['enum' => ['foo', 'bar']],
      ],
    ];
    $uiSchema = $this->createUiSchema($rule);

    $definition = new LayoutDefinition($uiSchema, $jsonSchema, FALSE, NULL);
    $conditionedDefinition = $definition->findControlDefinition('#/properties/conditioned');
    static::assertNotNull($conditionedDefinition);

    static::assertSame(
      [
        'visible' => [
          '[name="foo[bar]"]' => [
            [
              ['value' => 'foo'],
              'or',
              ['value' => 'bar'],
            ],
          ],
        ],
      ],
      $this->factory->createStatesArray($conditionedDefinition)
    );
  }

  public function testProperties(): void {
    $jsonSchema = (object) [
      'type' => 'object',
      'properties' => (object) [
        'conditioned' => (object) [
          'type' => 'string',
        ],
        'foo' => (object) [
          'type' => 'object',
          'properties' => (object) [
            'bar' => (object) [
              'type' => 'string',
            ],
          ],
        ],
      ],
    ];

    $rule = (object) [
      'effect' => 'ENABLE',
      'condition' => (object) [
        'scope' => '#/properties/foo',
        'schema' => (object) [
          'properties' => (object) [
            'bar' => (object) ['const' => 'test'],
          ],
        ],
      ],
    ];
    $uiSchema = $this->createUiSchema($rule);

    $definition = new LayoutDefinition($uiSchema, $jsonSchema, FALSE, NULL);
    $conditionedDefinition = $definition->findControlDefinition('#/properties/conditioned');
    static::assertNotNull($conditionedDefinition);

    static::assertSame(
      [
        'enabled' => [
          '[name="foo[bar]"]' => [
            ['value' => 'test'],
          ],
        ],
      ],
      $this->factory->createStatesArray($conditionedDefinition)
    );
  }

  public function testHideWithInteger(): void {
    $jsonSchema = (object) [
      'type' => 'object',
      'properties' => (object) [
        'conditioned' => (object) [
          'type' => 'string',
        ],
        'foo' => (object) [
          'type' => 'object',
          'properties' => (object) [
            'bar' => (object) [
              'type' => 'string',
            ],
          ],
        ],
      ],
    ];

    $rule = (object) [
      'effect' => 'HIDE',
      'condition' => (object) [
        'scope' => '#/properties/foo/properties/bar',
        'schema' => (object) ['const' => 12],
      ],
    ];
    $uiSchema = $this->createUiSchema($rule);

    $definition = new LayoutDefinition($uiSchema, $jsonSchema, FALSE, NULL);
    $conditionedDefinition = $definition->findControlDefinition('#/properties/conditioned');
    static::assertNotNull($conditionedDefinition);

    static::assertSame(
      [
        'invisible' => [
          '[name="foo[bar]"]' => [
            ['value' => '12'],
          ],
        ],
      ],
      $this->factory->createStatesArray($conditionedDefinition)
    );
  }

  public function testContains(): void {
    $jsonSchema = (object) [
      'type' => 'object',
      'properties' => (object) [
        'conditioned' => (object) [
          'type' => 'string',
        ],
        'foo' => (object) [
          'type' => 'object',
          'properties' => (object) [
            'bar' => (object) [
              'type' => 'array',
            ],
          ],
        ],
      ],
    ];

    $rule = (object) [
      'effect' => 'SHOW',
      'condition' => (object) [
        'scope' => '#/properties/foo/properties/bar',
        'schema' => (object) ['contains' => (object) ['const' => 'baz']],
      ],
    ];
    $uiSchema = $this->createUiSchema($rule);

    $definition = new LayoutDefinition($uiSchema, $jsonSchema, FALSE, NULL);
    $conditionedDefinition = $definition->findControlDefinition('#/properties/conditioned');
    static::assertNotNull($conditionedDefinition);

    static::assertSame(
      [
        'visible' => [
          [
            '[name="foo[bar][baz]"]' => [
              'checked' => TRUE,
            ],
          ],
        ],
      ],
      $this->factory->createStatesArray($conditionedDefinition)
    );
  }

  public function testContainsEnum(): void {
    $jsonSchema = (object) [
      'type' => 'object',
      'properties' => (object) [
        'conditioned' => (object) [
          'type' => 'string',
        ],
        'foo' => (object) [
          'type' => 'object',
          'properties' => (object) [
            'bar' => (object) [
              'type' => 'array',
            ],
          ],
        ],
      ],
    ];

    $rule = (object) [
      'effect' => 'SHOW',
      'condition' => (object) [
        'scope' => '#/properties/foo/properties/bar',
        'schema' => (object) [
          'contains' => (object) [
            'enum' => ['baz1', 'baz2'],
          ],
        ],
      ],
    ];
    $uiSchema = $this->createUiSchema($rule);

    $definition = new LayoutDefinition($uiSchema, $jsonSchema, FALSE, NULL);
    $conditionedDefinition = $definition->findControlDefinition('#/properties/conditioned');
    static::assertNotNull($conditionedDefinition);

    // phpcs:disable Squiz.Arrays.ArrayDeclaration.NoKeySpecified
    static::assertSame(
      [
        'visible' => [
          [
            '[name="foo[bar][baz1]"]' => [
              'checked' => TRUE,
            ],
            'or',
            '[name="foo[bar][baz2]"]' => [
              'checked' => TRUE,
            ],
          ],
        ],
      ],
      $this->factory->createStatesArray($conditionedDefinition)
    );
    // phpcs:enable
  }

  /**
   * @param array<string, mixed> $extraReferencedControlKeywords
   */
  private function createUiSchema(\stdClass $rule, array $extraReferencedControlKeywords = []): \stdClass {
    return (object) [
      'type' => 'test',
      'elements' => [
        (object) [
          'type' => 'Control',
          'scope' => '#/properties/conditioned',
          'rule' => $rule,
        ],
        (object) ([
          'type' => 'Control',
          'scope' => '#/properties/foo/properties/bar',
        ] + $extraReferencedControlKeywords),
      ],
    ];
  }

  public function testBoolWithCheckbox(): void {
    $jsonSchema = (object) [
      'type' => 'object',
      'properties' => (object) [
        'conditioned' => (object) [
          'type' => 'string',
        ],
        'foo' => (object) [
          'type' => 'object',
          'properties' => (object) [
            'bar' => (object) [
              'type' => 'boolean',
            ],
          ],
        ],
      ],
    ];

    // Test with TRUE.
    $rule = (object) [
      'effect' => 'HIDE',
      'condition' => (object) [
        'scope' => '#/properties/foo/properties/bar',
        'schema' => (object) ['const' => TRUE],
      ],
    ];
    $uiSchema = $this->createUiSchema($rule);

    $definition = new LayoutDefinition($uiSchema, $jsonSchema, FALSE, NULL);
    $conditionedDefinition = $definition->findControlDefinition('#/properties/conditioned');
    static::assertNotNull($conditionedDefinition);

    static::assertSame(
      [
        'invisible' => [
          '[name="foo[bar]"]' => [
            ['checked' => TRUE],
          ],
        ],
      ],
      $this->factory->createStatesArray($conditionedDefinition)
    );

    // Test with FALSE.
    $rule = (object) [
      'effect' => 'HIDE',
      'condition' => (object) [
        'scope' => '#/properties/foo/properties/bar',
        'schema' => (object) ['const' => FALSE],
      ],
    ];
    $uiSchema = $this->createUiSchema($rule);

    $definition = new LayoutDefinition($uiSchema, $jsonSchema, FALSE, NULL);
    $conditionedDefinition = $definition->findControlDefinition('#/properties/conditioned');
    static::assertNotNull($conditionedDefinition);

    static::assertSame(
      [
        'invisible' => [
          '[name="foo[bar]"]' => [
            ['checked' => FALSE],
          ],
        ],
      ],
      $this->factory->createStatesArray($conditionedDefinition)
    );
  }

  public function testBoolWithRadios(): void {
    $jsonSchema = (object) [
      'type' => 'object',
      'properties' => (object) [
        'conditioned' => (object) [
          'type' => 'string',
        ],
        'foo' => (object) [
          'type' => 'object',
          'properties' => (object) [
            'bar' => (object) [
              'type' => 'boolean',
            ],
          ],
        ],
      ],
    ];

    // Test with TRUE.
    $rule = (object) [
      'effect' => 'HIDE',
      'condition' => (object) [
        'scope' => '#/properties/foo/properties/bar',
        'schema' => (object) ['const' => TRUE],
      ],
    ];
    $uiSchema = $this->createUiSchema($rule, ['options' => (object) ['format' => 'radio']]);

    $definition = new LayoutDefinition($uiSchema, $jsonSchema, FALSE, NULL);
    $conditionedDefinition = $definition->findControlDefinition('#/properties/conditioned');
    static::assertNotNull($conditionedDefinition);

    static::assertSame(
      [
        'invisible' => [
          '[name="foo[bar]"]' => [
            ['value' => '1'],
          ],
        ],
      ],
      $this->factory->createStatesArray($conditionedDefinition)
    );

    // Test with FALSE.
    $rule = (object) [
      'effect' => 'HIDE',
      'condition' => (object) [
        'scope' => '#/properties/foo/properties/bar',
        'schema' => (object) ['const' => FALSE],
      ],
    ];
    $uiSchema = $this->createUiSchema($rule, ['options' => (object) ['format' => 'radio']]);

    $definition = new LayoutDefinition($uiSchema, $jsonSchema, FALSE, NULL);
    $conditionedDefinition = $definition->findControlDefinition('#/properties/conditioned');
    static::assertNotNull($conditionedDefinition);

    static::assertSame(
      [
        'invisible' => [
          '[name="foo[bar]"]' => [
            ['value' => '0'],
          ],
        ],
      ],
      $this->factory->createStatesArray($conditionedDefinition)
    );
  }

  /**
   * The UI schema doesn't contain the referenced control, but a control for an
   * object that implicitly contains the control.
   */
  public function testWithObject(): void {
    $jsonSchema = (object) [
      'type' => 'object',
      'properties' => (object) [
        'conditioned' => (object) [
          'type' => 'string',
        ],
        'foo' => (object) [
          'type' => 'object',
          'properties' => (object) [
            'bar' => (object) [
              'type' => 'string',
            ],
          ],
        ],
      ],
    ];

    $rule = (object) [
      'effect' => 'HIDE',
      'condition' => (object) [
        'scope' => '#/properties/foo/properties/bar',
        'schema' => (object) ['const' => 'bar'],
      ],
    ];
    $uiSchema = (object) [
      'type' => 'test',
      'elements' => [
        (object) [
          'type' => 'Control',
          'scope' => '#/properties/conditioned',
          'rule' => $rule,
        ],
        (object) [
          'type' => 'Control',
          'scope' => '#/properties/foo',
        ],
      ],
    ];

    $definition = new LayoutDefinition($uiSchema, $jsonSchema, FALSE, NULL);
    $conditionedDefinition = $definition->findControlDefinition('#/properties/conditioned');
    static::assertNotNull($conditionedDefinition);

    static::assertSame(
      [
        'invisible' => [
          '[name="foo[bar]"]' => [
            ['value' => 'bar'],
          ],
        ],
      ],
      $this->factory->createStatesArray($conditionedDefinition)
    );
  }

}
