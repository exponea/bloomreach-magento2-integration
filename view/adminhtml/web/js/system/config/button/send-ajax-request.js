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
            messageTypeClass: {
                success: 'message message-success success',
                error: 'message message-error error',
                notice: 'message message-notice notice'
            },
            requiredFieldsSelectors: '',
            disableButtonAfterClick: ''
        },

        /**
         * Init widget
         *
         * @private
         */
        _create: function () {
            if (this.options.buttonId && this.options.isEnabled) {
                $(this.options.buttonId).click(function (e) {
                    e.preventDefault();
                    this._ajaxSubmit();
                }.bind(this));

                if (this.options.requiredFieldsSelectors !== '') {
                    $(this.options.requiredFieldsSelectors).change(function () {
                        this._blockButtonIfConfigChanged();
                    }.bind(this));
                }
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
                    this._renderMessageContent(data.message, 'error');
                } else {
                    this._renderMessageContent(data.message, 'success');
                    if (this.options.disableButtonAfterClick) {
                        $(this.options.buttonId).prop('disabled', true);
                    }
                }
            }.bind(this)).fail(function () {
                this._renderMessageContent(
                    $t('An error occurred while processing your request. Please try again later.'),
                    'error'
                );
            }.bind(this));
        },

        /**
         * Render message content
         *
         * @param message
         * @param messageType
         * @private
         */
        _renderMessageContent: function (message, messageType) {
            var messageTypeClass = this.options.messageTypeClass,
                cssClass = messageTypeClass.hasOwnProperty(messageType) ? messageTypeClass[messageType] : '';

            message = '<div class="messages"><div class="' + cssClass + '">' + message + '</div></div>';
            $(this.options.messageContainer).html(message);
        },

        /**
         * Block button if required config changed
         *
         * @private
         */
        _blockButtonIfConfigChanged: function() {
            var button = $(this.options.buttonId);
            if (!button.is(":disabled")) {
                button.prop('disabled', true);
                this._renderMessageContent(
                    $t(
                        'Looks like the settings have changed. '
                        + 'Please save the settings or reload the page to make button active'
                    ),
                    'notice'
                );
            }
        }
    });

    return $.bloomreachEngagement.sendAjaxRequest;
});
