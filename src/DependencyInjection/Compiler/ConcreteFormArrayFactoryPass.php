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

namespace Drupal\json_forms\DependencyInjection\Compiler;

use Drupal\json_forms\Form\ConcreteFormArrayFactoryInterface;
use Drupal\json_forms\Form\FormArrayFactoryInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @codeCoverageIgnore
 */
final class ConcreteFormArrayFactoryPass implements CompilerPassInterface {

  public function process(ContainerBuilder $container): void {
    $factories = [];
    foreach ($container->findTaggedServiceIds('json_forms.concrete_form_array_factory') as $id => $tags) {
      $factories[$id] = $this->getPriority($container, $id);
    }

    arsort($factories);
    $factoryReferences = array_map(fn ($id) => new Reference($id), array_keys($factories));

    $formArrayFactoryDefinition = $container->getDefinition(FormArrayFactoryInterface::class);
    $formArrayFactoryDefinition->setArguments($factoryReferences);
  }

  private function getPriority(ContainerBuilder $container, string $id): int {
    $class = $this->getClass($container, $id);
    if (!is_a($class, ConcreteFormArrayFactoryInterface::class, TRUE)) {
      throw new RuntimeException(
        sprintf('Class "%s" does not implement "%s"', $class, ConcreteFormArrayFactoryInterface::class)
      );
    }

    return $class::getPriority();
  }

  private function getClass(ContainerBuilder $container, string $id): string {
    return $container->getDefinition($id)->getClass() ?? $id;
  }

}
