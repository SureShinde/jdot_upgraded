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

var config = {
    map: {
        '*': {
            'Magento_Checkout/js/action/select-payment-method': 'RLTSquare_TcsShipping/js/action/select-payment-method',
            'Magento_Checkout/js/model/shipping-save-processor/default': 'RLTSquare_TcsShipping/js/model/shipping-save-processor/default',
            'Magento_Checkout/js/action/get-payment-information': 'RLTSquare_TcsShipping/js/action/get-payment-information'
        }
    }
};