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

use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;
use Drupal\json_forms\JsonForms\Definition\Layout\LayoutDefinition;

final class DetailsArrayFactory extends AbstractLayoutArrayFactory {

  public static function getPriority() : int {
    return GroupArrayFactory::getPriority() + 1;
  }

  public function supportsDefinition(DefinitionInterface $definition): bool {
    // "closeable" is not part of original JSON Forms.
    return $definition instanceof LayoutDefinition
      && 'Group' === $definition->getType()
      && TRUE === $definition->getOptionsValue('closeable');
  }

  protected function createBasicFormArray(LayoutDefinition $definition): array {
    $form = [
      '#type' => 'details',
      '#title' => $definition->getKeywordValue('label'),
      '#open' => $definition->getOptionsValue('open', TRUE),
    ];

    if (NULL !== $definition->getKeywordValue('description')) {
      $form['#description'] = $definition->getKeywordValue('description');
    }

    return $form;
  }

}
