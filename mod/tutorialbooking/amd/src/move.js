// This file is part of the tutorial booking activity plugin.
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
 * A javascript module that allows tutorial booking slots to
 * be moved by dragging and dropping them.
 *
 * @module     mod_tutorialbooking/move
 * @package    mod_tutorialbooking
 * @copyright  2019 University of Nottingham
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/log', 'core/notification', 'core/ajax', 'core/sortable_list', 'core/str', 'core/templates'],
    function($, log, notification, ajax, SortableList, str, template) {
    /**
     * Stores CSS handles for various parts of Tutorial booking.
     * @type Object
     */
    var SELECTORS = {
        DRAGGABLE: '.tutorial_session',
        DRAGAREA: '.tutorial_sessions',
        HANDLEELEMENT: '.tutorial_session .sectionname',
        MOVEDOWNCONTROL: '.tutorial_session .controls .movedown',
        MOVEUPCONTROL: '.tutorial_session .controls .moveup'
    };

    /**
     * Stores the original order of the slots.
     * @type ElementList
     */
    var origOrder;

    /**
     * Sets up a tutorial booking page for drag and drop.
     *
     * @returns {undefined}
     */
    var init = function() {
        log.debug('Setting up', 'mod_tutorialbooking/dragdrop');
        // Remove the move controls.
        $(SELECTORS.MOVEUPCONTROL).remove();
        $(SELECTORS.MOVEDOWNCONTROL).remove();
        createHandles();
        var list = new SortableList(SELECTORS.DRAGAREA);
        list.getElementName = getSlotName;
        $(SELECTORS.DRAGAREA + ' ' + SELECTORS.DRAGGABLE).on(SortableList.EVENTS.DRAGSTART, startDrag)
                .on(SortableList.EVENTS.DROP, drop);
        log.debug('Setup completed', 'mod_tutorialbooking/dragdrop');
    };

    /**
     * Gets the name of a slot.
     *
     * @param {Element} element
     * @returns {Promise}
     */
    var getSlotName = function(element) {
        return $.Deferred().resolve(element.attr('data-name'));
    };

    /**
     * Generates the handle for the item.
     *
     * @returns {Promise}
     */
    var createHandles = function() {
        var promises = [];
        $(SELECTORS.HANDLEELEMENT).each(function(key, element) {
            var promise = str.get_string('moveslot', 'mod_tutorialbooking', element.innerText).then(function(string) {
                return template.render('core/drag_handle', {movetitle: string});
            }).then(function(html) {
                $(element).prepend($(html));
                return;
            }).fail(notification.exception);
            promises.push(promise);
        });
        return $.when.apply($, promises);
    };

    /**
     * Handles a drag start.
     *
     * @param {Event} e
     * @param {Object} info Information passed by the SortableList events
     * @returns {undefined}
     */
    var startDrag = function(e, info) {
        log.debug('Drag started', 'mod_tutorialbooking/move');
        // Remember position of the element in the beginning of dragging.
        origOrder = info.sourceList.children();
    };

    /**
     * Handles a drop event.
     *
     * Moves the slot to it's new position.
     *
     * @param {Event} e
     * @param {Object} info Information passed by the SortableList events
     * @returns {undefined}
     */
    var drop = function(e, info) {
        log.debug('Dropped element', 'mod_tutorialbooking/move');
        var newIndex = info.targetList.children().index(info.element);

        // Find the element being dragged.
        var tutorial = $(e.target).closest(SELECTORS.DRAGAREA);

        // Make the AJAX call to move the slot.
        var slot = info.element.attr('id');
        var target = $(origOrder.get(newIndex)).attr('id');
        var moveslot = {
            methodname: 'mod_tutorialbooking_moveslot',
            args: {
                tutorial: parseTutorialId(tutorial),
                slot: parseSlotId(slot),
                target: parseSlotId(target)
            }
        };
        var calls = ajax.call([moveslot]);
        calls[0].done(function(response) {
            if (response.success) {
                // Move the slot.
                if (response.where === 'before') {
                    var message = 'Slot ' + slot + ' moved before slot ' + target;
                    log.debug(message, 'mod_tutorialbooking/dragdrop');
                } else {
                    var message2 = 'Slot ' + slot + ' moved after slot ' + target;
                    log.debug(message2, 'mod_tutorialbooking/dragdrop');
                }
            }
        }).fail(notification.exception);
    };

    /**
     * Gets the id of the tutorial based on it's css id.
     *
     * They will always have a css id in the form: tutorial-<number>
     *
     * @param {DOMElement} element
     * @returns {Number}
     */
    var parseTutorialId = function(element) {
        return $(element).attr('id').substr(9);
    };

    /**
     * Gets the id of the slot based on it's css id.
     *
     * They will always have a css id in the form: slot-<number>
     *
     * @param {string} id
     * @returns {Number}
     */
    var parseSlotId = function(id) {
        return id.substr(5);
    };

    return {
        init: init
    };
});
