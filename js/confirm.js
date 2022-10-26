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

/**
 * Confirm dialog for form buttons.
 */
(function ($, Drupal, once) {
  Drupal.behaviors.formConfirm = {
    attach: function (context, settings) {

      function confirmDeferred(dialogOptions) {
        const defer = $.Deferred();

        const defaultDialogOptions = {
          title: Drupal.t('Confirm action'),
          text: Drupal.t('Are you sure?'),
          modal: true,
          autoOpen: true,
          draggable: false,
          resizable: false,
          // Define buttons as object so they can be modified in Drupal form array
          buttons: {
            confirm: {
              text: Drupal.t('Yes'),
              click: function () {
                defer.resolve();
                $(this).dialog('close');
              }
            },
            cancel: {
              text: Drupal.t('No'),
              click: function ()  {
                $(this).dialog('close');
              }
            }
          },
          close: function () {
            if (defer.state() === 'pending') {
              defer.reject();
            }
            $(this).remove();
          }
        };

        const finalDialogOptions = $.extend(true, {}, defaultDialogOptions, dialogOptions);
        // Convert buttons to array in conformance with API documentation
        finalDialogOptions.buttons = Object.values(finalDialogOptions.buttons);
        $('<div></div>').appendTo('body')
          .html('<div>' + finalDialogOptions.text + '</div>')
          .dialog(finalDialogOptions);

        return defer.promise();
      }

      function attachFormConfirm(context, settings) {
        Object.entries(settings.jsonFormsConfirm ?? {}).forEach(([className, dialogOptions]) => {
          once('json-forms-confirm', '.' + className, context).forEach((element) => {
            element.onclick = (event) => {
                event.preventDefault();
                confirmDeferred(dialogOptions).then(() => {
                  element.onclick = null;
                  element.click();
                });
              };
            }
          );
        });
      }

      attachFormConfirm(context, settings);
    }
  };

})(jQuery, Drupal, once);
