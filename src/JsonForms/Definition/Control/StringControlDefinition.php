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

namespace Drupal\json_forms\JsonForms\Definition\Control;

use Drupal\json_forms\Util\ConvertUtil;

class StringControlDefinition extends ControlDefinition {

  public function getMaxLength(): ?int {
    return ConvertUtil::stdClassToNull($this->propertySchema->maxLength ?? NULL);
  }

  public function getMinLength(): ?int {
    return ConvertUtil::stdClassToNull($this->propertySchema->minLength ?? NULL);
  }

  public function getPattern(): ?string {
    return ConvertUtil::stdClassToNull($this->propertySchema->pattern ?? NULL);
  }

}
