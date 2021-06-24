/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Shipping/js/model/config',
    'mage/url'

], function (Component, config, url) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Shipping/checkout/shipping/shipping-policy'
        },
        config: config(),
        getShippingPolicyUrl: function(){
            return url.build('deliveryandorders');
        }
    });
});