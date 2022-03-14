/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
define([
    'jquery',
    'mage/translate',
    'mage/template'
], function ($, $t) {
    'use strict';

    $.widget('bloomreachEngagement.sendAjaxRequest', {
        options: {
            ajaxUrl: '',
            isEnabled: false,
            buttonId: '',
            messageContainer: '',
            successClass: 'message message-success success',
            errorClass: 'message message-error error'
        },

        /**
         * Init widget
         *
         * @private
         */
        _create: function () {
            if (this.options.buttonId && this.options.isEnabled) {
                $('#' + this.options.buttonId).click(function (e) {
                    e.preventDefault();
                    this._ajaxSubmit();
                }.bind(this));
            }
        },

        /**
         * Send ajax request
         *
         * @private
         */
        _ajaxSubmit: function () {
            $.ajax({
                url: this.options.ajaxUrl,
                dataType: 'json',
                data: {
                    form_key: window.FORM_KEY
                },
                type: 'POST',
                showLoader: true
            }).done(function (data) {
                if (data.error) {
                    this._renderMessageContent(data.message, true);
                } else {
                    this._renderMessageContent(data.message, false);
                    $('#' + this.options.buttonId).prop('disabled', true);

                }
            }.bind(this)).fail(function () {
                this._renderMessageContent(
                    $t('An error occurred while processing your request. Please try again later.'),
                    true
                );
            }.bind(this));
        },

        /**
         * Render message content
         *
         * @param message
         * @param isError
         * @private
         */
        _renderMessageContent: function (message, isError) {
            var cssClass = isError ? this.options.errorClass : this.options.successClass;
            message = '<div class="messages"><div class="' + cssClass + '">' + message + '</div></div>';
            $('.' + this.options.messageContainer).html(message);
        }
    });

    return $.bloomreachEngagement.sendAjaxRequest;
});
