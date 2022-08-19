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

namespace Drupal\Tests\json_forms\Unit\Form\Control\Callbacks;

use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormState;
use Drupal\json_forms\Form\AbstractJsonFormsForm;
use Drupal\json_forms\Form\Control\Callbacks\RecalculateCallback;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\json_forms\Form\Control\Callbacks\RecalculateCallback
 */
final class RecalculateCallbackTest extends TestCase {

  /**
   * @var \Drupal\json_forms\Form\AbstractJsonFormsForm&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formMock;

  private FormState $formState;

  protected function setUp(): void {
    parent::setUp();
    $this->formMock = $this->createMock(AbstractJsonFormsForm::class);
    $this->formState = new FormState();
    $this->formState->setFormObject($this->formMock);
  }

  public function testOnChange(): void {
    $oldData = [
      'equal' => [
        'a' => 'old',
        'b' => 2,
        'c' => 3,
        'd' => 4.0,
        'e' => 5.1,
        'f' => '',
        'g' => NULL,
        'h' => NULL,
      ],
      'different' => [
        'a' => 'old',
        'b' => 2,
        'c' => 0,
        'd' => '0',
        'e' => '',
        'f' => NULL,
        'g' => 0,
        'h' => 'removed',
      ],
      'removed' => [
        'a' => ['b'],
      ],
    ];
    $this->addFormForAll($oldData, 'textfield');
    $oldData['notInForm1'] = 1;

    $newData = [
      'equal' => [
        'a' => 'old',
        'b' => 2,
        'c' => '3',
        'd' => 4,
        'e' => 5.1,
        'f' => NULL,
        'g' => NULL,
        'i' => NULL,
      ],
      'different' => [
        'a' => 'new',
        'b' => 3,
        'c' => '',
        'd' => '',
        'e' => 0,
        'f' => 0,
        'g' => FALSE,
        'i' => 'added',
      ],
      'added' => [
        'x' => ['y'],
      ],
    ];
    $this->addFormForAll($newData, 'textfield');
    $newData['notInForm2'] = 2;

    $this->formState->setValues($oldData);
    $this->formMock->expects(static::once())->method('calculateData')->with($this->formState)
      ->willReturn($newData);
    $form = [];
    $response = RecalculateCallback::onChange($form, $this->formState);

    $commands = $response->getCommands();
    static::assertCount(11, $commands);
    $i = 0;

    static::assertEquals(
      (new InvokeCommand('[name="different[a]"]', 'val', ['new']))->render(),
      $commands[$i++]
    );

    static::assertEquals(
      (new InvokeCommand('[name="different[b]"]', 'val', [3]))->render(),
      $commands[$i++]
    );

    static::assertEquals(
      (new InvokeCommand('[name="different[c]"]', 'val', ['']))->render(),
      $commands[$i++]
    );

    static::assertEquals(
      (new InvokeCommand('[name="different[d]"]', 'val', ['']))->render(),
      $commands[$i++]
    );

    static::assertEquals(
      (new InvokeCommand('[name="different[e]"]', 'val', [0]))->render(),
      $commands[$i++]
    );

    static::assertEquals(
      (new InvokeCommand('[name="different[f]"]', 'val', [0]))->render(),
      $commands[$i++]
    );

    static::assertEquals(
      (new InvokeCommand('[name="different[g]"]', 'val', [NULL]))->render(),
      $commands[$i++]
    );

    static::assertEquals(
      (new InvokeCommand('[name="different[h]"]', 'val', [FALSE]))->render(),
      $commands[$i++]
    );

    static::assertEquals(
      (new InvokeCommand('[name="different[i]"]', 'val', ['added']))->render(),
      $commands[$i++]
    );

    static::assertEquals(
      (new InvokeCommand('[name="removed[a][0]"]', 'val', [NULL]))->render(),
      $commands[$i++]
    );

    static::assertEquals(
      (new InvokeCommand('[name="added[x][0]"]', 'val', ['y']))->render(),
      $commands[$i++]
    );
  }

  public function testCheckboxUncheck(): void {
    $this->formState->set(['form', 'value'], ['#type' => 'checkbox']);
    $oldData = ['value' => TRUE];
    $newData = ['value' => NULL];

    $this->formState->setValues($oldData);
    $this->formMock->expects(static::once())->method('calculateData')->with($this->formState)
      ->willReturn($newData);
    $form = [];
    $response = RecalculateCallback::onChange($form, $this->formState);

    $expectedCommand = new InvokeCommand(
      '[name="value"]',
      'prop',
      ['checked', FALSE]
    );
    static::assertSame([$expectedCommand->render()], $response->getCommands());

  }

  public function testCheckboxCheck(): void {
    $this->formState->set(['form', 'value'], ['#type' => 'checkbox']);
    $oldData = ['value' => FALSE];
    $newData = ['value' => TRUE];

    $this->formState->setValues($oldData);
    $this->formMock->expects(static::once())->method('calculateData')->with($this->formState)
      ->willReturn($newData);
    $form = [];
    $response = RecalculateCallback::onChange($form, $this->formState);

    $expectedCommand = new InvokeCommand(
      '[name="value"]',
      'prop',
      ['checked', TRUE]
    );
    static::assertSame([$expectedCommand->render()], $response->getCommands());
  }

  public function testRadio(): void {
    $this->formState->set(['form', 'value'], ['#type' => 'radio']);
    $oldData = ['value' => 'old'];
    $newData = ['value' => 'new'];

    $this->formState->setValues($oldData);
    $this->formMock->expects(static::once())->method('calculateData')->with($this->formState)
      ->willReturn($newData);
    $form = [];
    $response = RecalculateCallback::onChange($form, $this->formState);

    $expectedCommand = new InvokeCommand(
      '[name="value"][value="new"]',
      'prop',
      ['checked', TRUE]
    );
    static::assertSame([$expectedCommand->render()], $response->getCommands());
  }

  public function testRadioNull(): void {
    $this->formState->set(['form', 'value'], ['#type' => 'radio']);
    $oldData = ['value' => 'old'];
    $newData = ['value' => NULL];

    $this->formState->setValues($oldData);
    $this->formMock->expects(static::once())->method('calculateData')->with($this->formState)
      ->willReturn($newData);
    $form = [];
    $response = RecalculateCallback::onChange($form, $this->formState);

    $expectedCommand = new InvokeCommand(
      '[name="value"]',
      'prop',
      ['checked', FALSE]
    );
    static::assertSame([$expectedCommand->render()], $response->getCommands());
  }

  /**
   * @param array<string, mixed> $data
   */
  private function addFormForAll(array $data, string $type): void {
    foreach ($data as $key => $value) {
      $this->doAddFormForAll($value, $type, $key);
    }
  }

  /**
   * @param mixed $data
   */
  private function doAddFormForAll($data, string $type, string $name): void {
    if (is_array($data)) {
      foreach ($data as $key => $value) {
        $this->doAddFormForAll($value, $type, $name . '[' . $key . ']');
      }
    }
    else {
      $this->formState->set(['form', $name], ['#type' => $type]);
    }
  }

}
