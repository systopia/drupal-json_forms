/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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
 * Performs a calculation when a form is initially loaded.
 */
(function (Drupal, once, $) {
  Drupal.behaviors.json_forms_initial_calculation = {
    attach: function (context, settings) {
      once('json-forms-initial-calculation', '[data-json-forms-init-calculation="1"]', context).forEach((element) => {
        if (element.disabled) {
          // Drupal enables the element after the AJAX call so we have to disable it again.
          const handler = function (event) {
            element.disabled = true;
            $(document).off('ajaxStop', handler);
          };
          $(document).on('ajaxStop', handler);
        }
        // Trigger a "change" event which results in an AJAX call that performs the calculations.
        element.dispatchEvent(new Event('change'));
      });
    }
  };

})(Drupal, once, jQuery);
