/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */

define([
    'jquery',
    'underscore',
    'Bloomreach_EngagementConnector/js/tracking/event-sender',
    'Magento_Catalog/js/price-box'
], function($, _, eventSender) {
    "use strict";

    $.widget('bloomreachEngagement.configurableTracking', {
        options: {
            ajaxUrl: '',
            priceBoxSelector: '[data-role=priceBox]',
            configurableOptionSelector: 'input[name="selected_configurable_option"]',
            configurableAttributeSelector: 'select.super-attribute-select',
            swatchesSelector: '[data-role=swatch-options]',
            trackingProvider: ''
        },
        eventCache: {},

        /**
         * Creating jquery widget to track product according to selected options
         */
        _create: function() {
            var priceBoxElement = $(this.options.priceBoxSelector),
                priceBox;

            if (!priceBoxElement.length) {
                return;
            }

            priceBox = priceBoxElement.data('magePriceBox');

            if (_.isUndefined(priceBox)) {
                return;
            }

            this.priceBox = priceBox;
            priceBoxElement.on('updatePrice', this.sendEvent.bind(this));
        },

        /**
         * Send event
         */
        sendEvent: function () {
            var productId = this.getOptionProductId();

            if (_.isUndefined(productId) || !this.checkSelectedOptions()) {
                return;
            }

            if (this.eventCache.hasOwnProperty(productId)) {
                eventSender.sendEvent(this.eventCache[productId], this.options.trackingProvider);
                return;
            }

            $.ajax({
                url: this.options.ajaxUrl,
                dataType: 'json',
                data: {
                    product_id: productId
                },
                type: 'POST'
            }).done(function (data) {
                if (!data.errorMessage) {
                    this.eventCache[productId] = data.event;
                    eventSender.sendEvent(data.event, this.options.trackingProvider);
                }
            }.bind(this));
        },

        /**
         * Check if all options were selected and get an ID of a selected product
         */
        getOptionProductId: function () {
            var swatches = $(this.options.swatchesSelector),
                optionIndex;

            if (swatches.length) {
                var swatchesData = swatches.data('mageSwatchRenderer');
                swatchesData = _.isUndefined(swatchesData) ? swatches.data('mage-SwatchRenderer') : swatchesData;

                if(_.isUndefined(swatchesData)) {
                    return optionIndex;
                }
                // check if all product options were selected and request can be sent
                var options = _.object(_.keys(swatchesData.optionsMap), {}),
                    attributeId;
                swatchesData.element.find('.' + swatchesData.options.classes.attributeClass + '[data-option-selected]')
                    .each(function () {
                        attributeId = $(this).attr('data-attribute-id');
                        options[attributeId] = $(this).attr('data-option-selected');
                    });
                optionIndex = _.findKey(swatchesData.options.jsonConfig.index, options);
            } else {
                optionIndex = $(this.options.configurableOptionSelector).val();
            }

            return optionIndex;
        },

        /**
         * Check if all options are selected
         *
         * @returns {boolean}
         */
        checkSelectedOptions: function () {
            var isSelected = true;

            $(this.options.configurableAttributeSelector).each(function() {
                if (!$(this).val()) {
                    isSelected = false;
                }
            });

            return isSelected;
        }
    });

    return $.bloomreachEngagement.configurableTracking;
});
