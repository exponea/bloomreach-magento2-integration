/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
define([], function () {
    'use strict';

    return {
        /**
         * Sends list of events to the Bloomreach
         *
         * @param eventList
         */
        sendListOfEvents: function(eventList) {
            if (typeof window.exponea !== 'undefined') {
                if (eventList.length > 0) {
                    eventList.forEach(function (event) {
                        this._send(event);
                    }.bind(this));
                }
            }
        },

        /**
         * Send one event to the Bloomreach
         *
         * @param event
         */
        sendEvent: function (event) {
            if (typeof window.exponea !== 'undefined') {
                this._send(event);
            }
        },

        /**
         * Send event to the Bloomreach
         *
         * @param event
         * @private
         */
        _send: function(event) {
            if ((typeof event.name !== 'undefined' ) && (typeof event.body !== 'undefined')) {
                window.exponea.track(event.name, event.body);
            }
        }

    }
});
