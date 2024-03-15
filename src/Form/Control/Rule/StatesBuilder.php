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

namespace Drupal\json_forms\Form\Control\Rule;

/**
 * @phpstan-type statesT array<string, array<string, array<array<string, mixed>|'and'|'or'|'xor'>>>
 * @phpstan-type conditionT array{checked: bool}|array{empty: bool}|array{value: scalar}
 */
final class StatesBuilder {

  /**
   * @phpstan-var statesT
   */
  private array $states = [];

  /**
   * @phpstan-param scalar|array<scalar> $value
   */
  public function add(
    string $effect,
    string $fieldName,
    $value,
    bool $negate
  ): self {
    foreach ($this->getStates($effect, $negate) as $state) {
      $selector = '[name="' . $fieldName . '"]';
      if (isset($this->states[$state][$selector])) {
        $this->states[$state][$selector][] = 'and';
      }

      $this->states[$state][$selector][] = $this->buildCondition($value);
    }

    return $this;
  }

  public function clear(): void {
    $this->states = [];
  }

  /**
   * @phpstan-return array<string>
   *
   * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
   */
  private function getStates(string $effect, bool $negate): array {
  // phpcs:enable
    switch ($effect) {
      case 'HIDE':
        return $negate ? ['visible'] : ['invisible'];

      case 'SHOW':
        return $negate ? ['invisible'] : ['visible'];

      case 'ENABLE':
        return $negate ? ['disabled'] : ['enabled'];

      case 'DISABLE':
        return $negate ? ['enabled'] : ['disabled'];

      default:
        return [];
    }
  }

  /**
   * @phpstan-param scalar|array<scalar>|null $value
   *
   * @phpstan-return conditionT|array<conditionT|'or'>
   */
  private function buildCondition($value): array {
    if (is_bool($value)) {
      return [
        ['checked' => $value],
        'and',
        ['value' => $value ? '1' : '0'],
      ];
    }

    if (NULL === $value) {
      return ['empty' => TRUE];
    }

    if (!is_array($value)) {
      return ['value' => (string) $value];
    }

    $condition = [];
    foreach ($value as $v) {
      if (!is_array($v)) {
        /** @phpstan-var conditionT $subCondition */
        $subCondition = $this->buildCondition($v);
        $condition[] = $subCondition;
        $condition[] = 'or';
      }
    }
    array_pop($condition);

    return $condition;
  }

  /**
   * @phpstan-return statesT
   */
  public function toArray(): array {
    return $this->states;
  }

}
