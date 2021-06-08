/*global alert*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, quote, priceUtils, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Xumulus_CartDiscount/checkout/summary/customprice'
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
            isDisplayed: function() {
                return this.isFullMode();
            },

            getSavingPrice: function() {
                var price = 0;
                var actualPrice = window.checkoutConfig.totalsData.actualprice;
                var savings = window.checkoutConfig.totalsData.savings;
                var totals = quote.getTotals()();
                var cart_totals = this.totals();
                var discount = cart_totals.discount_amount;
                var subtotal_saving = quote.subtotal;
                if (totals) {
                    subtotal_saving = totals.subtotal;
                }
                price = parseFloat(actualPrice) - parseFloat(savings);
                // if(parseFloat(discount) < 0){
                //     discount = parseFloat(discount) * -1;
                //     price = parseFloat(price) + parseFloat(discount)
                // }
                return price;
            },

            getValue: function() {
                var price = 0;
                if (this.totals() && typeof window.checkoutConfig.totalsData.actualprice != 'undefined') {
                    price = window.checkoutConfig.totalsData.actualprice;
                }

                return this.getFormattedPrice(price);
            },
            showPrice: function() {
              var price = 0;
              if (this.totals() && typeof window.checkoutConfig.totalsData.actualprice != 'undefined') {
                    var saving = this.getSavingPrice();
                    if(parseFloat(saving) < 0){
                        price = 0;
                    }else{
                         price = saving;
                    }
                }
                if(price > 0){
                    return true;
                }
                else{
                    return false;
                }
            }
        });
    }
);