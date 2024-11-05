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
 * Prevent switch of vertical tab on AJAX call.
 *
 * By default, Drupal switches to the last vertical tab that contains a
 * validation error on AJAX success. This is prevented by removing the CSS class
 * "error" during "ajaxStart" event and adding it back in "ajaxStop" event. The
 * approach to prevent the click on the link is not possible because event
 * handlers are executed in the order of attachment. (Ordering of Drupal
 * behaviors is not supported.)
 *
 * @see https://git.drupalcode.org/project/drupal/-/blob/10.3.6/core/misc/vertical-tabs.js?ref_type=tags#L132
 * @see https://git.drupalcode.org/project/drupal/-/blob/10.3.6/core/misc/ajax.js?ref_type=tags#L1135
 * @see https://www.drupal.org/project/drupal/issues/2474019
 */
(function ($) {

  let errorElements;

  $(document).on('ajaxStart', () => {
    errorElements = document.querySelectorAll('details .form-item .error');
    errorElements.forEach((item) => {
      item.classList.remove('error');
    });
  });

  $(document).on('ajaxStop', () => {
    errorElements.forEach((item) => {
      item.classList.add('error');
    });
  });

})(jQuery);
