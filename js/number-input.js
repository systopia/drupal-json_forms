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
 * Restricts input of number fields to digits, decimal separator, and minus.
 */
(function (Drupal, once) {
  Drupal.behaviors.json_forms_numberInput = {
    attach: function (context, settings) {
      // Decimal separator of browser.
      const decimalSeparator = Intl.NumberFormat()
        .formatToParts(1.1)
        .find(part => part.type === 'decimal')
        .value;

      const digits = [
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
      ];

      const allowedKeys = [
        'ArrowDown',
        'ArrowLeft',
        'ArrowRight',
        'ArrowUp',
        'Backspace',
        'Delete',
        'End',
        'Enter',
        'Home',
        'PageDown',
        'PageUp',
        'Tab',
      ];

      function getElementLang(element) {
        if (element.lang) {
          return element.lang;
        }

        return element.parentElement ? getElementLang(element.parentElement) : null;
      }

      once('json-forms-number-input', 'input[type="number"]', context).forEach((element) => {
        let decimalSeparators = decimalSeparator;
        const lang = getElementLang(element);
        if (lang) {
          // Decimal separator of element's language might be different from
          // the browser's separator. We allow both in that case. It depends on
          // the browser which one is preferred, i.e. the one that is used when
          // pressing the buttons to change a number.
          decimalSeparators += Intl.NumberFormat(lang)
            .formatToParts(1.1)
            .find(part => part.type === 'decimal')
            .value;
        }

        function isCharAllowed(char) {
          if (digits.includes(char)) {
            return true;
          }

          if ('-' === char) {
            if (!element.value.includes('-') && (element.min < 0 || element.min === '' || element.min == null)) {
              return true;
            }
          }
          else if (decimalSeparators.includes(char)) {
            if (!element.value.includes('.') && (element.step === 'any' || element.step < 1)) {
              return true;
            }
          }

          return false;
        }

        element.addEventListener('keydown', function (event) {
          if (!event.ctrlKey && !allowedKeys.includes(event.key) && !isCharAllowed(event.key)) {
            event.preventDefault();
          }
        });

        element.addEventListener('paste', function (event) {
          const data = event.clipboardData.getData('text/plain');
          if (data === '') {
            event.preventDefault();
            return;
          }

          for (let i = 0; i < data.length; i++) {
            if (!isCharAllowed(data[i])) {
              event.preventDefault();

              return;
            }
          }
        });
      });
    }
  };

})(Drupal, once);
