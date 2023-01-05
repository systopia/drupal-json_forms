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

namespace Drupal\Tests\json_forms\Unit\Form\Markup;

use Drupal\Core\Form\FormState;
use Drupal\json_forms\Form\FormArrayFactoryInterface;
use Drupal\json_forms\Form\Markup\HtmlMarkupArrayFactory;
use Drupal\json_forms\JsonForms\Definition\Markup\MarkupDefinition;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Drupal\json_forms\Form\Markup\HtmlMarkupArrayFactory
 * @covers \Drupal\json_forms\JsonForms\Definition\Markup\MarkupDefinition
 * @group json_forms
 */
final class HtmlMarkupArrayFactoryTest extends UnitTestCase {

  private HtmlMarkupArrayFactory $factory;

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject&\Drupal\json_forms\Form\FormArrayFactoryInterface
   */
  private MockObject $formArrayFactoryMock;

  private FormState $formState;

  protected function setUp(): void {
    parent::setUp();
    $this->factory = new HtmlMarkupArrayFactory();
    $this->formArrayFactoryMock = $this->createMock(FormArrayFactoryInterface::class);
    $this->formState = new FormState();
  }

  public function test(): void {
    $uiSchema = (object) [
      'type' => 'Markup',
      'contentMediaType' => 'text/html',
      'content' => '<em>test</em>',
      'label' => 'Label',
    ];

    $definition = new MarkupDefinition($uiSchema);
    static::assertTrue($this->factory->supportsDefinition($definition));

    $form = $this->factory->createFormArray($definition, $this->formState, $this->formArrayFactoryMock);
    static::assertEquals([
      '#type' => 'item',
      '#title' => 'Label',
      '#markup' => '<em>test</em>',
    ], $form);
  }

  public function testUnsupported(): void {
    $uiSchema = (object) [
      'type' => 'Markup',
      'contentMediaType' => 'text/markdown',
      'content' => '*test*',
      'label' => 'Label',
    ];

    $definition = new MarkupDefinition($uiSchema);
    static::assertFalse($this->factory->supportsDefinition($definition));
  }

}
