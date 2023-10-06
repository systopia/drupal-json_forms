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

/**
 * Ensure all AJAX call are finished before submit on button click. If this is
 * omitted the focused field (if any) is missing in the submitted data in case
 * the field was changed and performs an AJAX call on change.
 */
(function ($, Drupal, once) {
  Drupal.behaviors.jsonFormsSubmit = {
    attach: function (context) {
      function submit(event) {
        // If there's an active AJAX call wait for ajaxStop event.
        if ($.active > 0) {
          $(document).on('ajaxStop', () => event.target.click());
          event.preventDefault();
        }
      }

      function attachClick(context) {
        once('json-forms-submit', 'input[type="submit"]', context).forEach((element) => {
          element.onclick = submit;
        });
      }

      attachClick(context);
    }
  };

})(jQuery, Drupal, once);
