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

define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/payment-service',
        'mage/storage',
        'mage/url',
        'Magento_Checkout/js/action/get-totals'
    ],
    function(
        ko,
        $,
        quote,
        urlManager,
        paymentService,
        storage,
        urlBuilder,
        getTotalsAction
    ) {
        'use strict';

        return function (payment) {
            $('body').trigger('processStart');

            var serviceUrl = urlBuilder.build('rltsquare_shippingpayment/checkout/totals'); // Our controller to re-collect the totals

            var payload = {
                rltsPaymentMethod : payment
            }

            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            ).done(
                function(response) {
                    if(response) {
                        var deferred = $.Deferred();
                        getTotalsAction([], deferred);
                    }
                    $('body').trigger('processStop');
                }
            ).fail(
                function (response) {
                    $('body').trigger('processStop');
                }
            );
        }
    }
);