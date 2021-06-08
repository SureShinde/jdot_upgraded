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
        'jquery',
        'ko',
        'Magento_Checkout/js/model/quote',
        'RLTSquare_TcsShipping/js/action/checkout/cart/totals' // Our custom script
    ],
    function($, ko ,quote, totals) {
        'use strict';

        return function (paymentMethod) {
            quote.paymentMethod(paymentMethod);
            totals(paymentMethod['method']);
        }
    }
);