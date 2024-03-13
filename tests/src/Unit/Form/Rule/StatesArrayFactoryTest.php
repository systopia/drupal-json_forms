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
    $rule = (object) [
      'effect' => 'HIDE',
      'condition' => (object) [
        'scope' => '#/properties/foo/properties/bar',
        'schema' => (object) ['const' => 'bar'],
      ],
    ];

    static::assertSame(
      [
        'invisible' => [
          '[name="foo[bar]"]' => [
            ['value' => 'bar'],
          ],
        ],
      ],
      $this->factory->createStatesArray($rule)
    );
  }

  public function testNotHide(): void {
    $rule = (object) [
      'effect' => 'HIDE',
      'condition' => (object) [
        'scope' => '#/properties/foo/properties/bar',
        'schema' => (object) ['not' => (object) ['const' => 'bar']],
      ],
    ];

    static::assertSame(
      [
        'visible' => [
          '[name="foo[bar]"]' => [
            ['value' => 'bar'],
          ],
        ],
      ],
      $this->factory->createStatesArray($rule)
    );
  }

  public function testShowEnum(): void {
    $rule = (object) [
      'effect' => 'SHOW',
      'condition' => (object) [
        'scope' => '#/properties/foo/properties/bar',
        'schema' => (object) ['enum' => ['foo', 'bar']],
      ],
    ];

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
      $this->factory->createStatesArray($rule)
    );
  }

  public function testProperties(): void {
    $rule = (object) [
      'effect' => 'ENABLE',
      'condition' => (object) [
        'scope' => '#/properties/foo/properties/bar',
        'schema' => (object) [
          'properties' => (object) [
            'baz' => (object) ['const' => FALSE],
          ],
        ],
      ],
    ];

    static::assertSame(
      [
        'enabled' => [
          '[name="foo[bar][baz]"]' => [
            [
              ['checked' => FALSE],
              'and',
              ['value' => 0],
            ],
          ],
        ],
      ],
      $this->factory->createStatesArray($rule)
    );
  }

}
