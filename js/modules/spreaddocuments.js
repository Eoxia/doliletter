/* Copyright (C) 2026 EVARISK <technique@evarisk.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    js/modules/spreaddocuments.js
 * \ingroup doliletter
 * \brief   Documents tab of a spread: the "Add a document" button is the label of a hidden file
 *          input, the files picked are sent at once.
 */

'use strict';

/**
 * Init spreadDocuments JS
 *
 * @type {Object}
 */
window.doliletter.spreadDocuments = {};

/**
 * SpreadDocuments init
 *
 * @returns {void}
 */
window.doliletter.spreadDocuments.init = function() {
  window.doliletter.spreadDocuments.event();
};

/**
 * SpreadDocuments events
 *
 * @returns {void}
 */
window.doliletter.spreadDocuments.event = function() {
  $(document).on('change', '.spread-documents__add-input', window.doliletter.spreadDocuments.sendDocuments);
};

/**
 * Send the files picked, with a spinner on the button until the page comes back
 *
 * @returns {void}
 */
window.doliletter.spreadDocuments.sendDocuments = function() {
  if (this.files.length === 0) {
    return;
  }

  $(this).siblings('.spread-chip').addClass('spread-chip--busy').find('i').attr('class', 'fas fa-spinner fa-spin');
  this.form.submit();
};
