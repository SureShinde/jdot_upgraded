/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @api */
define([
    'Magento_Checkout/js/view/payment/default',
    'mage/url'
], function (Component, url) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_OfflinePayments/payment/cashondelivery'
        },

        /**
         * Returns payment method instructions.
         *
         * @return {*}
         */
        getInstructions: function () {
            return window.checkoutConfig.payment.instructions[this.item.method];
        },
        getBaseUrlReturns: function() {
            return url.build('returns');
        },
getBaseUrlShipping: function() {
            return url.build('deliveryandorders');
        },
getBaseUrlTermsandconditions: function() {
            return url.build('terms-and-conditions');
        }
    });
});
