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

namespace Drupal\json_forms\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition;
use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;

final class FormArrayFactory implements FormArrayFactoryInterface {

  /**
   * @var iterable<ConcreteFormArrayFactoryInterface>
   */
  private iterable $formArrayFactories;

  /**
   * @param ConcreteFormArrayFactoryInterface ...$formArrayFactories
   */
  public function __construct(ConcreteFormArrayFactoryInterface ...$formArrayFactories) {
    $this->formArrayFactories = $formArrayFactories;
  }

  public function createFormArray(DefinitionInterface $definition, FormStateInterface $formState): array {
    foreach ($this->formArrayFactories as $factory) {
      if ($factory->supportsDefinition($definition)) {
        $form = $factory->createFormArray($definition, $formState, $this);
        if ($definition instanceof ControlDefinition) {
          $formState->set(['form', $definition->getPropertyFormName()], $form);
        }

        return $form;
      }
    }

    throw new \InvalidArgumentException(sprintf('No factory found for "%s"', $definition->getType()));
  }

  public function supportsDefinition(DefinitionInterface $definition): bool {
    foreach ($this->formArrayFactories as $factory) {
      if ($factory->supportsDefinition($definition)) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
