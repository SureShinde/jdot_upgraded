define(function () {
    /*'use strict';*/

    return function (target) { // target == Result that 'Magento_Checkout/js/model/step-navigator' returns.
        // modify target
        var setShippingInformation = target.setShippingInformation;
        target.setShippingInformation = function() {
            if (target) { // before  method
              console.log('hi');
            }
            //alert('hi');
            console.log('hi');
            var result = setShippingInformation.apply();
            //after method call
            return  result;
        };
       return target;
    };
});