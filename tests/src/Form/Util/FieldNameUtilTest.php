<?php

/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

declare(strict_types = 1);

namespace Drupal\Tests\json_forms\Form\Util;

use Drupal\json_forms\Form\Util\FieldNameUtil;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\json_forms\Form\Util\FieldNameUtil
 */
final class FieldNameUtilTest extends TestCase {

  public function testToFormData(): void {
    $data = ['foo.bar' => ['foo bar' => 'baz']];
    $expected = ['foo:-:bar' => ['foo:_:bar' => 'baz']];
    static::assertSame($expected, FieldNameUtil::toFormData($data));
  }

  public function testToFormName(): void {
    static::assertSame('foo:-:bar', FieldNameUtil::toFormName('foo.bar'));
    static::assertSame('foo:_:bar', FieldNameUtil::toFormName('foo bar'));
    static::assertSame('foo:-:bar:_:baz', FieldNameUtil::toFormName('foo.bar baz'));
  }

  public static function testToFormParents(): void {
    $path = ['foo.bar', 2, 'bar baz', 3];
    $expected = ['foo:-:bar', 2, 'bar:_:baz', 3];
    static::assertSame($expected, FieldNameUtil::toFormParents($path));
  }

  public function testToJsonSchemaData(): void {
    $data = ['foo:-:bar' => ['foo:_:bar' => 'baz']];
    $expected = ['foo.bar' => ['foo bar' => 'baz']];
    static::assertSame($expected, FieldNameUtil::toJsonData($data));
  }

  public function testToJsonSchemaName(): void {
    static::assertSame('foo.bar', FieldNameUtil::toJsonName('foo:-:bar'));
    static::assertSame('foo bar', FieldNameUtil::toJsonName('foo:_:bar'));
    static::assertSame('foo.bar baz', FieldNameUtil::toJsonName('foo:-:bar:_:baz'));
  }

}
