/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
define([
    'jquery',
    'Magento_Ui/js/grid/columns/actions',
    'mage/template',
    'text!Bloomreach_EngagementConnector/template/grid/modal/list.html',
    'mage/translate',
    'Magento_Ui/js/modal/alert'
], function ($, Column, mageTemplate, listTpl, $t, uiAlert) {
    'use strict';

    return Column.extend({
        modal: {},
        progressAction: 'view_progress',

        /**
         * @inheritDoc
         */
        defaultCallback: function (actionIndex, recordId, action) {
            var modalType = action.rowIndex + actionIndex;
            if (typeof this.modal[modalType] === 'undefined') {
                var row = this.rows[action.rowIndex],
                    modalHtml = actionIndex !== this.progressAction ? this._getModalHtml(row[actionIndex]) : '';

                this.modal[modalType] = $('<div class="bloomreach-ec-modal-' + actionIndex + '"></div>')
                    .html(modalHtml)
                    .modal({
                        type: 'slide',
                        innerScroll: true,
                        title: action.label,
                        buttons: []
                    });
            }

            if (actionIndex === this.progressAction) {
                this._renderProgressModal(this.modal[modalType], action);
            } else {
                this.modal[modalType].trigger('openModal');
            }
        },

        /**
         * Get modal html
         *
         * @param rowContent
         * @returns {*}
         */
        _getModalHtml: function (rowContent) {
            var template = mageTemplate(listTpl);

            return template({list: rowContent});
        },

        /**
         * Render progress modal
         *
         * @param modal
         * @param action
         * @private
         */
        _renderProgressModal: function (modal, action) {
            $.ajax({
                type: 'POST',
                url: action.url,
                data: action.requestParams,
                showLoader: true
            }).done(function (response) {
                if (response.success) {
                    modal.html(response.content).trigger('openModal');
                }
            }.bind(this)).fail(function (response) {
                if (response.status === 403) {
                    uiAlert({
                        content: $t(
                            'You don\'t have permission to view %entity_name import progress'
                        ).replace(
                            '%entity_name',
                            action.entityName
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
                        'Failed to load %entity_name initial import progress. Please try again later'
                    ).replace(
                        '%entity_name',
                        action.entityName
                    )
                });
            });
        }
    });
});
