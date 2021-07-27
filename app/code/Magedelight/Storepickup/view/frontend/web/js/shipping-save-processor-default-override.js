
/**
 * Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category Magedelight
 * @package Magedelight_Storepickup
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

/*global define,alert*/
define(
        [
            'ko',
            'Magento_Checkout/js/model/quote',
            'Magento_Checkout/js/model/resource-url-manager',
            'mage/storage',
            'Magento_Checkout/js/model/payment-service',
            'Magento_Checkout/js/model/payment/method-converter',
            'Magento_Checkout/js/model/error-processor',
            'Magento_Checkout/js/model/full-screen-loader',
            'Magento_Checkout/js/action/select-billing-address'
        ],
        function (
                ko,
                quote,
                resourceUrlManager,
                storage,
                paymentService,
                methodConverter,
                errorProcessor,
                fullScreenLoader,
                selectBillingAddressAction
                ) {
            'use strict';

            return {
                saveShippingInformation: function () {
                    var payload;

                    var method = quote.shippingMethod().method_code;
                    if (method =='storepickup') {

                        var address = jQuery('[name="store-address"]').val();
                        var addobj = jQuery.parseJSON(address);
                        quote.shippingAddress().customerAddressId = 0;
                        quote.shippingAddress().firstname = addobj[0].storename;
                        quote.shippingAddress().middlename = addobj[0].storelocator_id;
                        quote.shippingAddress().lastname ="Store";
                        quote.shippingAddress().street[0] = addobj[0].address;
                        quote.shippingAddress().street[1] = " ";
                        quote.shippingAddress().city = addobj[0].city;
                        
                        if (addobj[0].state) {
                            quote.shippingAddress().region = addobj[0].state;    
                        }else{
                            if (addobj[0].region_id) {
                                quote.shippingAddress().regionId = addobj[0].region_id;
                                quote.shippingAddress().regionCode = addobj[0].region_code;
                            };    
                        };
                        
                        quote.shippingAddress().countryId = addobj[0].country;
                        quote.shippingAddress().postcode = addobj[0].zipcode;
                        quote.shippingAddress().telephone = addobj[0].telephone;

                        if (istimeslotenable) {
                            var pickupdate = jQuery('[name="pickup_date"]').val() + jQuery('[name="store-time"]').val();
                        }else{
                            var pickupdate = jQuery('[name="pickup_date"]').val();
                        }
                            payload = {
                                addressInformation: {
                                    shipping_address: quote.shippingAddress(),
                                    billing_address: quote.billingAddress(),
                                    shipping_method_code: quote.shippingMethod().method_code,
                                    shipping_carrier_code: quote.shippingMethod().carrier_code,
                                    extension_attributes: {
                                        pickup_store: jQuery('[name="pickup_store"]').val(),
                                        pickup_date: pickupdate,
                                    }
                                }
                            };

                    }else{

                        if (!quote.billingAddress()) {
                            selectBillingAddressAction(quote.shippingAddress());
                        }
                            payload = {
                            addressInformation: {
                                shipping_address: quote.shippingAddress(),
                                billing_address: quote.billingAddress(),
                                shipping_method_code: quote.shippingMethod().method_code,
                                shipping_carrier_code: quote.shippingMethod().carrier_code,
                                extension_attributes: {
                                    pickup_store: '',
                                    pickup_date: '',
                                }
                            }
                        };
                    }

                    fullScreenLoader.startLoader();

                    return storage.post(
                        resourceUrlManager.getUrlForSetShippingInformation(quote),
                        JSON.stringify(payload)
                    ).done(
                        function (response) {
                            quote.setTotals(response.totals);
                            paymentService.setPaymentMethods(methodConverter(response.payment_methods));
                            fullScreenLoader.stopLoader();
                        }
                    ).fail(
                        function (response) {
                            errorProcessor.process(response);
                            fullScreenLoader.stopLoader();
                        }
                    );
                }
            };
        }
);
