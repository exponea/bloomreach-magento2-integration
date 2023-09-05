/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
define([
    'Bloomreach_EngagementConnector/js/tracking/exponea-sender',
    'Bloomreach_EngagementConnector/js/tracking/datalayer-sender'
], function (exponeaSender, datalayerSender) {
    'use strict';

    return {
        /**
         * Sends list of events to the Bloomreach
         *
         * @param eventList
         * @param trackingProvider
         */
        sendListOfEvents: function(eventList, trackingProvider) {
            switch (trackingProvider) {
                case 'exponea':
                    exponeaSender.sendListOfEvents(eventList);
                    break;
                case 'dataLayer':
                    datalayerSender.sendListOfEvents(eventList);
                    break;
                case 'all':
                    exponeaSender.sendListOfEvents(eventList);
                    datalayerSender.sendListOfEvents(eventList);
                    break;
            }
        },

        /**
         * Send one event to the Bloomreach
         *
         * @param event
         * @param trackingProvider
         */
        sendEvent: function(event, trackingProvider) {
            switch (trackingProvider) {
                case 'exponea':
                    exponeaSender.sendEvent(event);
                    break;
                case 'dataLayer':
                    datalayerSender.sendEvent(event);
                    break;
                case 'all':
                    exponeaSender.sendEvent(event);
                    datalayerSender.sendEvent(event);
                    break;
            }
        }
    }
});
