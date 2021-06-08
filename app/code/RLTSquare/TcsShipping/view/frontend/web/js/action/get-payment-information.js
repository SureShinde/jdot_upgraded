/**
 * NOTICE OF LICENSE
 * You may not sell, distribute, sub-license, rent, lease or lend complete or portion of software to anyone.
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @package   RLTSquare_TcsShipping
 * @copyright Copyright (c) 2018 RLTSquare (https://www.rltsquare.com)
 * @contacts  support@rltsquare.com
 * @license  See the LICENSE.md file in module root directory
 */

define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/payment/method-converter',
    'Magento_Checkout/js/model/payment-service',
    'RLTSquare_TcsShipping/js/action/checkout/cart/totals'
], function ($, quote, urlBuilder, storage, errorProcessor, customer, methodConverter, paymentService, totals) {
    'use strict';

    return function (deferred, messageContainer) {
        var serviceUrl;

        deferred = deferred || $.Deferred();

        /**
         * Checkout for guest and registered customer.
         */
        if (!customer.isLoggedIn()) {
            serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/payment-information', {
                cartId: quote.getQuoteId()
            });
        } else {
            serviceUrl = urlBuilder.createUrl('/carts/mine/payment-information', {});
        }

        return storage.get(
            serviceUrl, false
        ).done(function (response) {
            quote.setTotals(response.totals);
            paymentService.setPaymentMethods(methodConverter(response['payment_methods']));
            deferred.resolve();

            var selectedPaymentMethod = null;
            try {
                selectedPaymentMethod = quote.paymentMethod();
            } catch (e) {
            }

            if (selectedPaymentMethod)
                totals(selectedPaymentMethod['method']);

        }).fail(function (response) {
            errorProcessor.process(response, messageContainer);
            deferred.reject();
        });
    };
});
