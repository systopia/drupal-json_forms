<?php

/*
 * Copyright (C) 2022 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Drupal\json_forms\Form\Validation;

use Opis\JsonSchema\Validator;
use Systopia\ExpressionLanguage\SystopiaExpressionLanguage;
use Systopia\JsonSchema\Expression\SymfonyExpressionHandler;
use Systopia\JsonSchema\SystopiaValidator;

final class OpisValidatorFactory {

  private static Validator $validator;

  public static function getValidator(): Validator {
    if (!isset(self::$validator)) {
      $expressionHandler = new SymfonyExpressionHandler(new SystopiaExpressionLanguage());
      self::$validator = new SystopiaValidator([
        'calculator' => $expressionHandler,
        'evaluator' => $expressionHandler,
      ], 20);
    }

    return self::$validator;
  }

}
