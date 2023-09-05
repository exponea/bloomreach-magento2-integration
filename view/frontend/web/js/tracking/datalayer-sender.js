/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
define([], function () {
    'use strict';

    return {
        /**
         * Init Datalayer
         *
         * @private
         */
        _initDataLayer: function () {
            window.dataLayer = window.dataLayer || [];
        },

        /**
         * Sends list of events to the Datalayer
         *
         * @param eventList
         */
        sendListOfEvents: function (eventList) {
            this._initDataLayer();

            if (eventList.length > 0) {
                eventList.forEach(function (event) {
                    this._send(event);
                }.bind(this));
            }
        },

        /**
         * Send one event to the Datalayer
         *
         * @param event
         */
        sendEvent: function (event) {
            this._send(event);
        },

        /**
         * Send event to the Datalayer
         *
         * @param event
         * @private
         */
        _send: function (event) {
            if ((typeof event.name !== 'undefined') && (typeof event.body !== 'undefined')) {
                window.dataLayer.push(
                    {
                        'namespace': 'exponea',
                        'event_name': event.name,
                        'event_properties': event.body
                    }
                )
            }
        }
    }
});
