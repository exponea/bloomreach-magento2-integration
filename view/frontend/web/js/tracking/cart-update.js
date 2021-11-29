/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */

define([
    'Bloomreach_EngagementConnector/js/tracking/event-sender',
    'Magento_Customer/js/customer-data',
], function(eventSender, customerData) {
    "use strict";
    var cartUpdate = {
        eventVersionKey: 'bloomreachCartEventsVersion',
        eventDataKey: 'bloomreachCartEventsData',

        /**
         * Sends cart events
         */
        sendEvents: function () {
            var cart = customerData.get('cart');

            cart.subscribe(function (data) {
                this._send(data);
            }.bind(this));
        },

        /**
         * Send events
         *
         * @param data
         * @private
         */
        _send: function (data) {
            if (typeof data.bloomreachEvents !== 'undefined'
                && this._isNewEventsVersion(data.bloomreachEvents)
            ) {
                eventSender.sendListOfEvents(data.bloomreachEvents.eventsList);
                this._setEventsData(data.bloomreachEvents);
            }
        },

        /**
         * Checks whether is a new events data
         *
         * @param data
         * @returns {boolean}
         * @private
         */
        _isNewEventsVersion: function (data) {
            return window.localStorage.getItem(this.eventVersionKey) !== data.version
                && window.localStorage.getItem(this.eventDataKey) !== JSON.stringify(data.eventsList);
        },

        /**
         * Sets events data to the local storage
         *
         * @param data
         * @private
         */
        _setEventsData: function (data) {
            window.localStorage.setItem(this.eventVersionKey, data.version);
            window.localStorage.setItem(this.eventDataKey, JSON.stringify(data.eventsList));
        }
    };

    return cartUpdate.sendEvents();
});
