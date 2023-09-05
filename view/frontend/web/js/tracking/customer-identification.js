/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';

    $.widget('bloomreachEngagement.customerIdentification', {
        options: {
            registered: {}
        },

        /**
         * Create widget
         *
         * @private
         */
        _create: function () {
            if (typeof window.exponea !== 'undefined') {
                let customer = customerData.get('customer'),
                    customerInfo = customer(),
                    self = this;

                if (Object.keys(customerInfo).length !== 0) {
                    this._identifyCustomer(customerInfo);
                } else {
                    customerData.reload(['customer'], false)
                        .done(function () {
                            self._identifyCustomer(customer());
                        })
                        .fail(function () {
                            customer.subscribe(function (data) {
                                self._identifyCustomer(data);
                                this.dispose();
                            });
                        });
                }
            }
        },

        /**
         * Identify customer
         *
         * @param customer
         * @private
         */
        _identifyCustomer: function (customer) {
            let registered = this._getRegisteredData(customer);
            if (Object.keys(registered).length !== 0) {
                window.exponea.identify(registered);
            }
        },

        /**
         * Returns registered data
         *
         * @param customer
         * @returns {*}
         * @private
         */
        _getRegisteredData: function(customer) {
            let registered = (typeof customer.registered !== 'undefined') ? customer.registered : {};

            return this._mergeRegistered(registered);
        },

        /**
         * Merge registered
         *
         * @param registered
         * @private
         */
        _mergeRegistered: function(registered) {
            return $.extend({}, this.options.registered, registered);
        }
    });

    return $.bloomreachEngagement.customerIdentification;
});
