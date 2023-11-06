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

namespace Drupal\json_forms\Form\Validation\Translation;

use Drupal\Core\Language\LanguageManagerInterface;
use Opis\JsonSchema\Errors\ValidationError;
use Systopia\JsonSchema\Translation\TranslatorFactory;
use Systopia\JsonSchema\Translation\TranslatorInterface;

/**
 * @codeCoverageIgnore
 */
final class DrupalJsonSchemaValidationTranslator implements TranslatorInterface {

  private LanguageManagerInterface $languageManager;

  private string $lastLanguageId = '';


  /**
   * @phpstan-ignore-next-line Not initialized in constructor.
   */
  private TranslatorInterface $translator;

  public function __construct(LanguageManagerInterface $languageManager) {
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritDoc}
   */
  public function trans(string $id, array $parameters, ValidationError $error): string {
    return $this->getTranslator()->trans($id, $parameters, $error);
  }

  private function getTranslator(): TranslatorInterface {
    if ($this->languageManager->getCurrentLanguage()->getId() !== $this->lastLanguageId) {
      $this->lastLanguageId = $this->languageManager->getCurrentLanguage()->getId();
      $this->translator = TranslatorFactory::createTranslator($this->lastLanguageId);
    }

    return $this->translator;
  }

}
