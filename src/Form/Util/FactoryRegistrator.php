<?php

/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Drupal\json_forms\Form\Util;

use Assert\Assertion;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\json_forms\Form\ConcreteFormArrayFactoryInterface;

/**
 * @codeCoverageIgnore
 */
final class FactoryRegistrator {

  /**
   * Registers all implementations of ConcreteFormArrayFactoryInterface.
   *
   * All PSR conform classes below the given directory (recursively) are
   * considered.
   */
  public static function registerFactories(ContainerBuilder $container, string $dir, string $namespace): void {
    Assertion::directory($dir);

    $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
    while ($it->valid()) {
      if ($it->isFile() && 'php' === $it->getFileInfo()->getExtension()) {
        // @phpstan-ignore-next-line
        $class = static::getClass($namespace, $it->getInnerIterator());
        if (static::isFactoryClass($class)) {
          /** @phpstan-var class-string $class */
          static::registerFactory($container, $class);
        }
      }

      $it->next();
    }
  }

  /**
   * @phpstan-param class-string $class
   */
  public static function registerFactory(ContainerBuilder $container, string $class): void {
    if (!$container->has($class)) {
      $container->autowire($class)->addTag('json_forms.concrete_form_array_factory');
    }
    elseif (!$container->getDefinition($class)->hasTag('json_forms.concrete_form_array_factory')) {
      $container->getDefinition($class)->addTag('json_forms.concrete_form_array_factory');
    }
  }

  private static function getClass(string $namespace, \RecursiveDirectoryIterator $it): string {
    $class = $namespace . '\\';
    if ('' !== $it->getSubPath()) {
      $class .= str_replace('/', '\\', $it->getSubPath()) . '\\';
    }

    return $class . $it->getFileInfo()->getBasename('.php');
  }

  private static function isFactoryClass(string $class): bool {
    if (!class_exists($class)) {
      return FALSE;
    }

    $reflClass = new \ReflectionClass($class);

    return $reflClass->implementsInterface(ConcreteFormArrayFactoryInterface::class)
      && !$reflClass->isAbstract();
  }

}
