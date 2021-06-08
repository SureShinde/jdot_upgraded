define(
    [
        'Xumulus_CartDiscount/js/view/checkout/summary/customprice'
    ],
    function (Component) {
        'use strict';
 
        return Component.extend({
            /**
             * @override
             * use to define amount is display setting
             */
            isDisplayed: function () {
                return true;
            }
        });
    }
);