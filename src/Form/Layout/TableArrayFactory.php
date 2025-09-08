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

declare(strict_types=1);

namespace Drupal\json_forms\Form\Layout;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;
use Drupal\json_forms\JsonForms\Definition\Layout\LayoutDefinition;

final class TableArrayFactory extends AbstractLayoutArrayFactory {

  use StringTranslationTrait;

  /**
   * {@inheritDoc}
   */
  protected function createBasicFormArray(LayoutDefinition $definition): array {
    return [
      '#type' => 'table',
      '#input' => FALSE,
      '#header' => $this->getHeader($definition),
      '#empty' => $definition->getKeywordValue('empty') ?? $this->t('No data available'),
    ];
  }

  public function supportsDefinition(DefinitionInterface $definition): bool {
    return $definition instanceof LayoutDefinition && 'Table' === $definition->getType();
  }

  /**
   * @phpstan-return array<string|array<string, mixed>>
   */
  private function getHeader(LayoutDefinition $definition): array {
    $header = $definition->getKeywordValue('header');
    /** @phpstan-var array<string|null> $header */
    foreach ($header as &$label) {
      if (NULL === $label) {
        // Use no space for column header.
        $label = ['style' => ['padding: 0;']];
      }
    }

    /** @phpstan-var array<string|array<string, mixed>> $header */
    // @phpstan-ignore varTag.type
    return $header;
  }

}
