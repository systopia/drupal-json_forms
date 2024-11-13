/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

/**
 * Adding/removing entries to on array before a previous AJAX call has been
 * finished might lead to an inconsistent state. Thus, buttons are disabled
 * during AJAX calls. Additionally, form submit is not possible during AJAX
 * calls. Fields that initiate an AJAX calls are disabled until the call is
 * finished and would be missing in the submitted data.
 */
(function ($) {

  $(document).on('ajaxStart', () => {
    const buttons = document.querySelectorAll('input[type="submit"]:enabled, input[type="button"]:enabled');
    buttons.forEach((button) => {
      button.disabled = true;
      button.setAttribute('data-ajax-disabled', 'true');
    });
  });

  $(document).on('ajaxStop', () => {
    const buttons = document.querySelectorAll('input[data-ajax-disabled="true"]');
    buttons.forEach((button) => {
      button.disabled = false;
      buttom.removeAttribute('data-ajax-disabled');
    });

  });

})(jQuery);
