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
 * \file    js/doliletter.js
 * \ingroup doliletter
 * \brief   Entry point of the DoliLetter scripts of the back office, bundled with js/modules/ into
 *          js/doliletter.min.js, which saturne_header() loads. The envelope pages keep their own
 *          js/doliletter.js.php.
 */

'use strict';

if (!window.doliletter) {
  /**
   * Init DoliLetter JS
   *
   * @type {Object}
   */
  window.doliletter = {};

  /**
   * Init scriptsLoaded DoliLetter
   *
   * @type {Boolean}
   */
  window.doliletter.scriptsLoaded = false;
}

if (!window.doliletter.scriptsLoaded) {
  /**
   * DoliLetter init
   *
   * @returns {void}
   */
  window.doliletter.init = function() {
    window.doliletter.load_list_script();
  };

  /**
   * Load all modules' init
   *
   * @returns {void}
   */
  window.doliletter.load_list_script = function() {
    if (!window.doliletter.scriptsLoaded) {
      let key = undefined;
      for (key in window.doliletter) {
        if (window.doliletter[key] && window.doliletter[key].init) {
          window.doliletter[key].init();
        }
      }
      window.doliletter.scriptsLoaded = true;
    }
  };

  $(document).ready(window.doliletter.init);
}
