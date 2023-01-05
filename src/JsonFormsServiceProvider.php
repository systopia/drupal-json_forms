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

namespace Drupal\json_forms;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Drupal\json_forms\DependencyInjection\Compiler\ConcreteFormArrayFactoryPass;
use Drupal\json_forms\Form\Util\FactoryRegistrator;

/**
 * @codeCoverageIgnore
 */
final class JsonFormsServiceProvider implements ServiceProviderInterface {

  public function register(ContainerBuilder $container): void {
    $container->addCompilerPass(new ConcreteFormArrayFactoryPass());

    FactoryRegistrator::registerFactories(
      $container,
      __DIR__ . '/Form',
      'Drupal\\json_forms\\Form'
    );
  }

}
