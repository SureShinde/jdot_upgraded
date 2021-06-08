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
                template: 'Xumulus_CartDiscount/checkout/summary/cartrulesummery'
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
            isDisplayed: function() {
                return this.isFullMode();
            },

            getValue: function() {
                var html = '';
                if (this.totals() && typeof window.checkoutConfig.totalsData.cartruleapplied != 'undefined') {
                    html = window.checkoutConfig.totalsData.cartruleapplied;
                }

                return html;
            },
            showPrice: function() {
                if (this.totals() && typeof window.checkoutConfig.totalsData.cartruleapplied != 'undefined') {
                    return true
                }
                else{
                    return false;
                }
            }
        });
    }
);