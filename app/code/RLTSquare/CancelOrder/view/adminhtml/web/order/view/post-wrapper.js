/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'mage/url'
], function ($, confirm, urlBuilder) {
    'use strict';


    /**
     * @param {String} url
     * @returns {Object}
     */
    function getForm(url) {
        return $('<form>', {
            'action': url,
            'method': 'POST'
        }).append($('<input>', {
            'name': 'form_key',
            'value': window.FORM_KEY,
            'type': 'hidden'
        })).append($('<input>', {
            'name': 'reason',
            'value': $('#orderCancelComment').val(),
            'type': 'text',
            'required' : true
        })).append($('<input>', {
            'name': 'description',
            'value': $('#orderCancelDescription').val(),
            'type': 'text',
        })).append($('<input>', {
            'name': 'products_restock',
            'value': $('#orderProductsRestock').val(),
            'type': 'text',
        }));
    }

    $('#order-view-cancel-button').click(function () {
        var msg = $.mage.__(window.orderCancelStatuses),
            url = $('#order-view-cancel-button').data('url');
        confirm({
            'content': msg,
            'actions': {

                /**
                 * 'Confirm' action handler.
                 */
                confirm: function () {
                    // getCustomForm(url).appendTo('body').submit();
                    getForm(url).appendTo('body').submit();
                }
            }
        });

        return false;
    });

    $('#order-view-hold-button').click(function () {
        var url = $('#order-view-hold-button').data('url');

        getForm(url).appendTo('body').submit();
    });

    $('#order-view-unhold-button').click(function () {
        var url = $('#order-view-unhold-button').data('url');

        getForm(url).appendTo('body').submit();
    });

});
