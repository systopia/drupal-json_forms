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
use Drupal\json_forms\Form\Control\ArrayArrayFactory;
use Drupal\json_forms\Form\Control\CheckboxArrayFactory;
use Drupal\json_forms\Form\Control\DateArrayFactory;
use Drupal\json_forms\Form\Control\DatetimeArrayFactory;
use Drupal\json_forms\Form\Control\EmailArrayFactory;
use Drupal\json_forms\Form\Control\HiddenArrayFactory;
use Drupal\json_forms\Form\Control\NumberArrayFactory;
use Drupal\json_forms\Form\Control\ObjectArrayFactory;
use Drupal\json_forms\Form\Control\RadiosArrayFactory;
use Drupal\json_forms\Form\Control\SelectArrayFactory;
use Drupal\json_forms\Form\Control\StringArrayFactory;
use Drupal\json_forms\Form\Control\SubmitButtonArrayFactory;
use Drupal\json_forms\Form\Control\UrlArrayFactory;
use Drupal\json_forms\Form\Layout\CategorizationArrayFactory;
use Drupal\json_forms\Form\Layout\CategoryArrayFactory;
use Drupal\json_forms\Form\Layout\GroupArrayFactory;
use Drupal\json_forms\Form\Layout\HorizontalLayoutArrayFactory;
use Drupal\json_forms\Form\Layout\VerticalLayoutArrayFactory;
use Drupal\json_forms\Form\Markup\HtmlMarkupArrayFactory;
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
    // @todo Use tagged services and dependency injection
    $this->formArrayFactories = array_merge($formArrayFactories, [
      new CategorizationArrayFactory(),
      new CategoryArrayFactory(),
      new GroupArrayFactory(),
      new HorizontalLayoutArrayFactory(),
      new VerticalLayoutArrayFactory(),
      new HtmlMarkupArrayFactory(),
      new HiddenArrayFactory(),
      new ArrayArrayFactory(),
      new SubmitButtonArrayFactory(),
      new CheckboxArrayFactory(),
      new DateArrayFactory(),
      new DatetimeArrayFactory(),
      new EmailArrayFactory(),
      new RadiosArrayFactory(),
      new SelectArrayFactory(),
      new NumberArrayFactory(),
      new UrlArrayFactory(),
      new StringArrayFactory(),
      new ObjectArrayFactory(),
    ]);
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
