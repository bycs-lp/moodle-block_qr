// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * QR code renderer for block_qr.
 *
 * @module     block_qr/qr_renderer
 * @copyright  2026 ISB Bayern
 * @author     Thomas Schönlein
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* global QRCode */

import Modal from 'core/modal';

/**
 * Initialises QR rendering for one block instance.
 * @param {string} id Block context ID (used for DOM element IDs)
 * @param {string} content QR code content
 * @param {string} description Modal title
 */
export const init = (id, content, description) => {
    if (typeof QRCode === 'undefined') {
        return;
    }

    const cfg = {content, color: '#000000', background: '#ffffff', ecl: 'M', container: 'svg-viewbox', join: true};
    const svg = new QRCode(cfg).svg();

    const container = document.getElementById('container' + id);
    if (container) {
        container.innerHTML = svg;
    }

    const btn = document.getElementById('qrcodeModalButton' + id);
    if (btn) {
        btn.addEventListener('click', () => {
            Modal.create({title: description, body: svg}).then(m => m.show());
        });
    }
};
