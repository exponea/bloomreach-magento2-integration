/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/modal'
], function (Column, $, $t, uiAlert, confirm) {
    'use strict';

    return Column.extend({
        defaults: {
            template: 'Bloomreach_EngagementConnector/template/grid/columns/action',
        },

        modal: [],
        record: {},

        /**
         * Runs action
         *
         * @param {Object} record - Data to be preprocessed.
         */
        runAction: function (record) {
            var link = this.getLink(record),
                previewLink = this.getPreviewLink(record),
                requestParams = this.getRequestParams(record),
                self = this;

            this.record = record;

            if (this.isConfirmationRequired(record)) {
                confirm({
                    title: this.getEntityName(),
                    content: $t(
                        'Are you sure you want to %action initial import?'
                    ).replace(
                        '%action',
                        this.getActionType()
                    ),
                    actions: {
                        confirm: function () {
                            self.sendAjaxRequest(link, requestParams);
                        }
                    }
                });
            } else if (previewLink) {
                if (typeof this.modal[this.getModalType()] !== 'undefined') {
                    this.displayPreview('', link, requestParams, this.getButtonLabel(record));

                    return;
                }

                this.sendAjaxRequest(previewLink, requestParams);
            } else if (link) {
                this.sendAjaxRequest(link, requestParams);
            }
        },

        /**
         * Get request params
         *
         * @param record
         * @returns {{}|*}
         */
        getRequestParams: function (record) {
            if (typeof record[this.index]['requestParams'] !== 'undefined') {
                return record[this.index]['requestParams'];

            }

            return {};
        },

        /**
         * Get link
         *
         * @param {Object} record - Data to be preprocessed.
         * @returns {String}
         */
        getLink: function (record) {
            if (typeof record[this.index]['link'] !== 'undefined') {
                return record[this.index]['link'];
            }

            return '';
        },

        /**
         * Get link
         *
         * @param {Object} record - Data to be preprocessed.
         * @returns {String}
         */
        getPreviewLink: function (record) {
            if (typeof record[this.index]['preview_link'] !== 'undefined') {
                return record[this.index]['preview_link'];
            }

            return '';
        },

        /**
         * Get css class
         *
         * @param {Object} record - Data to be preprocessed.
         * @returns {String}
         */
        getCssClass: function (record) {
            if (typeof record[this.index]['cssClass'] !== 'undefined') {
                return record[this.index]['cssClass'];
            }

            return '';
        },

        /**
         * Get button label
         *
         * @param {Object} record - Data to be preprocessed.
         * @returns {String}
         */
        getButtonLabel: function (record) {
            if (typeof record[this.index]['label'] !== 'undefined') {
                return record[this.index]['label'];
            }

            return '';
        },

        /**
         * Checks if confirmation is required
         *
         * @param {Object} record - Data to be preprocessed.
         * @returns {Boolean}
         */
        isConfirmationRequired: function(record) {
            if (typeof record[this.index]['confirmation'] !== 'undefined') {
                return !!record[this.index]['confirmation'];
            }

            return false;
        },

        /**
         * Get entity type
         *
         * @returns {String}
         */
        getEntityType: function() {
            if (typeof this.record['entity_type'] !== 'undefined') {
                return this.record['entity_type'];
            }

            return '';
        },

        /**
         * Get entity name
         *
         * @returns {String}
         */
        getEntityName: function() {
            if (typeof this.record[this.index]['entityName'] !== 'undefined') {
                return this.record[this.index]['entityName'];
            }

            return '';
        },

        /**
         * Get entity name
         *
         * @returns {String}
         */
        getActionType: function() {
            if (typeof this.record[this.index]['actionType'] !== 'undefined') {
                return this.record[this.index]['actionType'];
            }

            return '';
        },

        /**
         * Get modal title
         *
         * @returns {String}
         */
        getModalTitle: function() {
            if (typeof this.record[this.index]['modal_title'] !== 'undefined') {
                return this.record[this.index]['modal_title'];
            }

            return '';
        },

        /**
         * Is Button visible
         *
         * @param {Object} record - Data to be preprocessed.
         * @returns {boolean}
         */
        isButtonVisible: function (record) {
            return this.getButtonLabel(record) !== '' && this.getLink(record) !== '';
        },

        /**
         * Send Ajax request
         *
         * @param url
         * @param params
         */
        sendAjaxRequest: function (url, params) {
            var self = this;
            $.ajax({
                type: 'POST',
                url: url,
                data: params,
                showLoader: true
            }).done(function (response) {
                if (response.success) {
                    this.handleSuccessResponse(response);
                }
            }.bind(this)).fail(function (response) {
                if (response.status === 403) {
                    uiAlert({
                        content: $t(
                            'You don\'t have permission to %action_type %entity_name'
                        ).replace(
                            '%action_type',
                            self.getActionType()
                        ).replace(
                            '%entity_name',
                            self.getEntityName()
                        )
                    });

                    return;
                } else if (response.responseJSON !== null
                    && typeof response.responseJSON.success !== 'undefined'
                    && !response.responseJSON.success
                    && typeof response.responseJSON.message !== 'undefined'
                ) {
                    uiAlert({
                        content: response.responseJSON.message
                    });

                    return;
                }

                uiAlert({
                    content: $t(
                        'Failed to %action_type %entity_name. Please try again later'
                    ).replace(
                        '%action_type',
                        self.getActionType()
                    ).replace(
                        '%entity_name',
                        self.getEntityName()
                    )
                });
            });
        },

        /**
         * Handle Response
         *
         * @param {Object} response - Data to be preprocessed.
         */
        handleSuccessResponse: function (response) {
            if (typeof response.preview_content === 'undefined') {
                window.location.reload();
                return;
            }

            this.displayPreview(
                response.preview_content,
                this.getLink(this.record),
                this.getRequestParams(this.record),
                this.getButtonLabel(this.record)
            );
        },

        /**
         * Display preview content
         *
         * @param content
         * @param url
         * @param params
         */
        displayPreview: function (content, url, params) {
            var self = this;
            if (typeof this.modal[this.getModalType()] === 'undefined') {
                var modalHtml = this.getModalHtml(content);

                this.modal[this.getModalType()] = $('<div></div>')
                    .html(modalHtml)
                    .modal({
                        type: 'popup',
                        innerScroll: true,
                        title: self.getModalTitle(),
                        responsive: true,
                        modalClass: 'confirm',
                        buttons: [
                            {
                                text: $.mage.__('Confirm'),
                                class: 'action-primary action-accept',
                                attr: {},

                                click: function (event) {
                                    self.sendAjaxRequest(url, params);
                                    this.closeModal(event);
                                }
                            }
                        ]
                    });
            }

            this.modal[this.getModalType()].trigger('openModal');
        },

        /**
         * Get modal html
         *
         * @param previewContent
         * @returns {*}
         */
        getModalHtml: function (previewContent) {
            return '<p style="padding-top: 20px">' +
                $t('You can download a sample import file from the following link:') +
                '<p><a href="' + previewContent.file_url + '">' +
                '<strong>' + $t('Sample import file') + '</strong>' +
                '</a></p></p>' +
                '<p style="padding-top: 20px">' +
                $t('Click the <strong>Confirm</strong> button to start the import configuration') +
                '</p>';
        },

        /**
         * Get modal type
         *
         * @returns {string}
         */
        getModalType: function () {
            return this.getEntityType() + '-preview_content';
        }
    });
});
