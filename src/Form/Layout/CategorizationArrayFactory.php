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

namespace Drupal\json_forms\Form\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\json_forms\Form\FormArrayFactoryInterface;
use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;
use Drupal\json_forms\JsonForms\Definition\Layout\LayoutDefinition;

final class CategorizationArrayFactory extends AbstractLayoutArrayFactory {

  private int $count = 0;

  private string $group = '';

  public function createFormArray(
    DefinitionInterface $definition,
    FormStateInterface $formState,
    FormArrayFactoryInterface $formArrayFactory
  ): array {
    $oldGroup = $this->group;
    $this->group = '_categorization' . $this->count++;
    try {
      return parent::createFormArray($definition, $formState, $formArrayFactory);
    }
    finally {
      $this->group = $oldGroup;
    }
  }

  public function supportsDefinition(DefinitionInterface $definition): bool {
    return $definition instanceof LayoutDefinition && 'Categorization' === $definition->getType();
  }

  protected function createBasicFormArray(LayoutDefinition $definition): array {
    // The categories (details elements of the vertical_tabs element) MUST
    // NOT be children of the vertical_tabs element, thus we add the group name
    // as key (which also makes usage of #parents unnecessary).
    return [
      $this->group => [
        '#type' => 'vertical_tabs',
      ],
    ];
  }

  protected function createElementFormArray(
    DefinitionInterface $element,
    FormStateInterface $formState,
    FormArrayFactoryInterface $formArrayFactory
  ): array {
    return ['#group' => $this->group] + parent::createElementFormArray($element, $formState, $formArrayFactory);
  }

}
