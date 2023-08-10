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

class NumberControlDefinition extends ControlDefinition {

  public function getMultipleOf(): ?int {
    return ConvertUtil::stdClassToNull($this->getpropertySchema->multipleOf ?? NULL);
  }

  public function getMaximum(): ?int {
    return ConvertUtil::stdClassToNull($this->propertySchema->maximum ?? NULL);
  }

  public function getExclusiveMaximum(): ?int {
    return ConvertUtil::stdClassToNull($this->propertySchema->exclusiveMaximum ?? NULL);
  }

  public function getMinimum(): ?int {
    return ConvertUtil::stdClassToNull($this->propertySchema->minimum ?? NULL);
  }

  public function getExclusiveMinimum(): ?int {
    return ConvertUtil::stdClassToNull($this->propertySchema->exclusiveMinimum ?? NULL);
  }

  /**
   * @return int|null Not a standardized property.
   */
  public function getPrecision(): ?int {
    return ConvertUtil::stdClassToNull($this->propertySchema->precision ?? NULL);
  }

}
