/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'ko',
        'uiComponent',
        'jquery',
        'mage/translate',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/model/quote'
    ],
    function (ko,Component,$,$t,modal,quote) {
        "use strict";
        var quoteWarrantyData = window.checkoutConfig.totalsData;
        return Component.extend({
            defaults: {
                template: 'Xumulus_CartDiscount/sample/discountsummary',
                selector: '[data-role=checkout-remove-link]',
            },
            quoteWarrantyData: quoteWarrantyData,
            getValue: function (quoteItem) {
                return quoteItem.name;
            },
            getAppliedRule: function () {
                var item = quoteWarrantyData.cartruleapplied;
                return item;
            },
            
            getItem: function () {
                var itemElement = '';
                var obj = this;
                console.log('new Update');
                var title = this.getErrorTitle(this.quoteWarrantyData.warranty_error_message);
                if (typeof this.quoteWarrantyData.removewarranty !== 'undefined') {
                    _.each(this.quoteWarrantyData.removewarranty, function(element, index) {
                            itemElement = itemElement + obj.createItemHtml(element , index , obj.quoteWarrantyData.removewarranty.length);
                    });
                    if (typeof itemElement !== 'undefined' && itemElement.length > 12) {
                       itemElement = title + itemElement;
                    } else {
                       itemElement = ''; 
                    }
                }
                return itemElement;
            },
            createItemHtml: function (element, index, count) {
                var address = quote.shippingAddress();
                var last = '';
                if (index === count) {
                    last = 'last';
                }
                if (address.countryId != '' && this.inArray(address.regionId,element.region_ids) === false) {
                    var data_params = {};
                    data_params['itemid'] = element.itemid;
                    data_params['quote'] = element.quote;
                    data_params['warranty'] = element.warranty;
                    data_params['page'] = element.page;
                    var url = "'"+element.remove_url+"'";
                    var string_param = "'"+JSON.stringify(data_params)+"'";
                    return '<div class="warranty-details  '+last+'">'+
                            '<input type="hidden" name="warranty_addeded_quote_'+element.itemid+'" id="warranty_addeded_quote_'+element.itemid+'" value="'+element.itemid+'" />'+
                            '<div class="warranty-product-detail">'+
                            '<span class="product-name">'+element.product_name+'</span>'+
                            '<span class="waranty-plan">'+element.warranty_plan+'</span>'+                    
                            '</div>'+
                            '<div class="warranty-remove-counter">'+
                            '<span class="checkout-remove-link cart table-wrapper" id="checkout-remove-link-'+element.itemid+'" data-params = '+string_param+' data-url="'+element.remove_url+'" onclick="removeCheckoutWarranty(this,'+url+')">'+
                            '<span class ="actions-toolbar" ><a class="action action-delete"><span>Delete</span></a></span>'+                  
                            '</span>'+
                            '</div>'+
                        '</div>';
               }
               return '';
                
            },
            getErrorTitle: function (message) {
                return '<div class="warranty-list-title">'+message+'</div>';
            },
            inArray: function (needle, haystack) {
                var length = haystack.length;
                for(var i = 0; i < length; i++) {
                    if(haystack[i] == needle) return true;
                }
                return false;
            },
        });
    }
);
