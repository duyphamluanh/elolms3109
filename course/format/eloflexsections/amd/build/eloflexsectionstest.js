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
 * Javascript controller for the "Actions" panel at the bottom of the page.
 *
 * @package    format_eloflexsections
 * @author     Jean-Roch Meurisse
 * @copyright  2018 University of Namur - Cellule TICE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/log', 'core/str'], function($, log, str) {

    "use strict";

    /**
     * Update toggles state of current course in browser storage.
     */
    var setState = function(course, toggles, storage) {
        if (storage == 'local') {
            window.localStorage.setItem('sections-toggle-' + course, JSON.stringify(toggles));
        } else if (storage == 'session') {
            window.sessionStorage.setItem('sections-toggle-' + course, JSON.stringify(toggles));
        }
    };

    /**
     * Update toggles state of current course in browser storage.
     */
    var getState = function(course, storage) {
        var toggles;
        if (storage == 'local') {
            toggles = window.localStorage.getItem('sections-toggle-' + course);
        } else if (storage == 'session') {
            toggles = window.sessionStorage.getItem('sections-toggle-' + course);
        }
        if (toggles === null) {
            return {};
        } else {
            return JSON.parse(toggles);
        }
    };

    return {
        init: function(args) {
            log.debug('Format eloflexsections AMD module initialized');
            $(document).ready(function($) {
                // debugger;
                var sectiontoggles;
                var keepstateoversession = args.keepstateoversession;
                var storage;
                if (keepstateoversession == 1) {
                    // Use browser local storage.
                    storage = 'local';
                } else {
                    // Use browser session storage.
                    storage = 'session';
                }
                sectiontoggles = getState(args.course, storage);
                // Toan
                // setTimeout(function() {
                //     var i = $('.sectiontoggle');
                //     $('.sectiontoggle').each(function (index) {
                //         var section = '#collapse-' + (index + 1);
                //         if ($(section).parent().parent().hasClass('current') || (index + 1) in sectiontoggles) {
                //             $(section).collapse('show');
                //         } else {
                //             $(section).collapse('hide');
                //         }
                //     });
                // }, 500);

                // Duy
                setTimeout(function() {
                    // debugger;
                    Object.keys(sectiontoggles).forEach(sectiontoggle => {
                        let section = "#"+sectiontoggle;
                        $(section).collapse('show');
                    });
                }, 100);
                
                $('.collapse').on('show.bs.collapse', function(event) {
                    var sectionstringid = $(event.target).attr('id');
                    var sectionid = sectionstringid.substring(sectionstringid.lastIndexOf('-') + 1);

                    if (!sectiontoggles.hasOwnProperty(sectionid)) {
                        sectiontoggles[sectionid] = "true";
                        setState(args.course, sectiontoggles, storage);
                    }
                });
                $('.collapse').on('hide.bs.collapse', function(event) {
                    var sectionstringid = $(event.target).attr('id');
                    var sectionid = sectionstringid.substring(sectionstringid.lastIndexOf('-') + 1);

                    if (sectiontoggles.hasOwnProperty(sectionid)) {
                        delete sectiontoggles[sectionid];
                        setState(args.course, sectiontoggles, storage);
                    }
                });
            });
        }
    };
});
