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

namespace Drupal\json_forms\Form\Util;

use Drupal\Core\Form\FormStateInterface;

final class FormStatePropertyAccessor {

  private FormStateInterface $formState;

  /**
   * @var array<int|string>
   */
  private array $propertyPath;

  /**
   * @param \Drupal\Core\Form\FormStateInterface $formState
   * @param array<int|string> $propertyPath
   */
  public function __construct(FormStateInterface $formState, array $propertyPath) {
    $this->formState = $formState;
    $this->propertyPath = $propertyPath;
  }

  /**
   * @param \Drupal\Core\Form\FormStateInterface $formState
   * @param array<int|string> $propertyPath
   *
   * @return static
   */
  public static function create(FormStateInterface $formState, array $propertyPath): self {
    return new self($formState, $propertyPath);
  }

  /**
   * @param string $key
   *
   * @return mixed
   */
  public function getProperty(string $key) {
    return $this->formState->get($this->buildPropertyKey($key));
  }

  /**
   * Returns the property with the given key.
   *
   * It will be set to the specified value if it's not yet set.
   *
   * @param string $key
   * @param mixed $value
   *
   * @return mixed
   */
  public function getOrSetProperty(string $key, $value) {
    if (!$this->hasProperty($key)) {
      $this->setProperty($key, $value);
    }

    return $this->getProperty($key);
  }

  public function hasProperty(string $key): bool {
    return $this->formState->has($this->buildPropertyKey($key));
  }

  /**
   * @param string $key
   * @param mixed $value
   *
   * @return $this
   */
  public function setProperty(string $key, $value): self {
    $this->formState->set($this->buildPropertyKey($key), $value);

    return $this;
  }

  /**
   * @param string $key
   *
   * @return array<int|string>
   */
  private function buildPropertyKey(string $key): array {
    return array_merge(['_'], $this->propertyPath, [$key]);
  }

}
