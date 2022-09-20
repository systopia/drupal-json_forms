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

namespace Drupal\json_forms\Form\Control\Callbacks;

use Assert\Assertion;
use Drupal\Core\Form\FormStateInterface;
use Drupal\json_forms\Form\Util\FormStatePropertyAccessor;
use Drupal\json_forms\Form\Util\FormValueAccessor;

final class ArrayCallbacks {

  /**
   * @param array<int|string, mixed> $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  public static function addItem(array &$form, FormStateInterface $formState): void {
    $triggeringElement = $formState->getTriggeringElement();
    Assertion::keyExists($triggeringElement, '#_controlPropertyPath');
    $propertyPath = $triggeringElement['#_controlPropertyPath'];

    $propertyAccessor = FormStatePropertyAccessor::create($formState, $propertyPath);
    $numItems = $propertyAccessor->getProperty('numItems');
    Assertion::integer($numItems);
    $propertyAccessor->setProperty('numItems', $numItems + 1);

    // Calculation is required for calculated data to be still set because
    // calculated values are not part of the POSTed data since they are marked
    // as disabled.
    /** @var \Drupal\json_forms\Form\AbstractJsonFormsForm $formObject */
    $formObject = $formState->getFormObject();
    $data = $formObject->calculateData($formState);
    $formState->setTemporary($data);

    $formState->setRebuild();
  }

  /**
   * @param array<int|string, mixed> $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *
   * @return array<int|string, mixed>
   */
  public static function ajaxAdd(array &$form, FormStateInterface $formState): array {
    $triggeringElement = $formState->getTriggeringElement();
    Assertion::isArray($triggeringElement);

    return self::getArrayForm($form, $triggeringElement);
  }

  /**
   * @param array<int|string, mixed> $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *
   * @return array<int|string, mixed>
   */
  public static function ajaxRemove(array &$form, FormStateInterface $formState): array {
    // Changing properties in $formState has no effect in ajax callback, so we
    // combine it with removeItem().
    $triggeringElement = $formState->getTriggeringElement();
    Assertion::keyExists($triggeringElement, '#_controlPropertyPath');
    $propertyPath = $triggeringElement['#_controlPropertyPath'];

    $arrayForm = &self::getArrayForm($form, $triggeringElement);
    FormValueAccessor::setValue($arrayForm['items'], $propertyPath, $formState->getTemporaryValue($propertyPath));

    return $arrayForm;
  }

  /**
   * @param array<int|string, mixed> $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  public static function removeItem(array &$form, FormStateInterface $formState): void {
    /*
     * Changing values in $form and $formState has no effect in validation
     * callback, though the last array item is not available in the ajax
     * callback, anymore. So we use temporary values and change the values in
     * ajaxRemove(). The used approach ensures that the resulting array has
     * contiguous indexes and thus will be treated as array in JSON, not as
     * object.
     */
    $triggeringElement = $formState->getTriggeringElement();
    Assertion::keyExists($triggeringElement, '#_controlPropertyPath');
    $propertyPath = $triggeringElement['#_controlPropertyPath'];
    $propertyAccessor = FormStatePropertyAccessor::create($formState, $propertyPath);

    $name = $triggeringElement['#name'];
    $pos = strrpos($name, '_');
    Assertion::integer($pos);
    $indexToRemove = (int) substr($name, $pos + 1);

    $arrayItems = $formState->getValue($propertyPath);
    Assertion::isArray($arrayItems);
    unset($arrayItems[$indexToRemove]);
    $arrayItems = array_values($arrayItems);

    // Calculation is required for calculated data to be still set because
    // calculated values are not part of the POSTed data since they are marked
    // as disabled.
    $formState->setValue($propertyPath, $arrayItems);
    /** @var \Drupal\json_forms\Form\AbstractJsonFormsForm $formObject */
    $formObject = $formState->getFormObject();
    $data = $formObject->calculateData($formState);
    $formState->setTemporary($data);

    $numItems = $propertyAccessor->getProperty('numItems');
    Assertion::integer($numItems);
    $propertyAccessor->setProperty('numItems', $numItems - 1);

    $formState->setRebuild();
  }

  /**
   * @param array<int|string, mixed> $form
   * @param array<int|string, mixed> $triggeringElement
   *
   * @return array<int|string, mixed>
   *   The form array created for property of type "array" for the given
   *   triggering element, i.e. add/remove button.
   */
  private static function &getArrayForm(array &$form, array $triggeringElement): array {
    $arrayParents = $triggeringElement['#array_parents'];
    Assertion::isArray($arrayParents);
    $propertyPath = $triggeringElement['#_controlPropertyPath'];
    Assertion::isArray($propertyPath);

    $ref = &$form;
    foreach ($arrayParents as $parent) {
      if (($ref['#parents'] ?? NULL) === $propertyPath) {
        return $ref;
      }

      $ref = &$ref[$parent];
      Assertion::isArray($ref);
    }

    throw new \InvalidArgumentException(
      sprintf('Form for triggering element with property path "%s" not found', json_encode($propertyPath))
    );
  }

}
