define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'jquery',
    'mage/translate'
], function (Component, quote, $, $t) {
    'use strict';


    return Component.extend({
        defaults: {
            template: 'Transiteo_Taxes/summary/surcharge_checkout_taxes'
        },
        totals: quote.getTotals(),

        updateValues: function() {
            var url = window.checkoutConfig.transiteo_checkout_taxes_url
            // fetch states with country id
            var param = "quote=" + window.checkoutConfig.quote_id;
            $.ajax({
                url: url,
                async: false,
                data: param,
                type: "POST",
                dataType: "json",
                error(data) {
                    console.log("no taxes response !");
                }
            }).done(function (data){
                window.checkoutConfig.transiteo_duty = data.transiteo_duty;
                window.checkoutConfig.transiteo_vat = data.transiteo_vat;
                window.checkoutConfig.transiteo_total_taxes = data.transiteo_total_taxes;
                window.checkoutConfig.transiteo_special_taxes = data.transiteo_special_taxes;
                window.checkoutConfig.transiteo_incoterm = data.transiteo_incoterm;
            });
        },

        /**
         * @return {*|Boolean}
         */
        isDisplayed: function (myValue) {

            if(myValue === 'transiteo_incoterm' ){
                return (window.checkoutConfig.transiteo_total_taxes !== null && window.checkoutConfig.transiteo_total_taxes !== 0);
            }
            if(myValue === 'transiteo_duty'){
                this.updateValues();
            }

            //get pure value
            const pureValue = this.getPureValue(myValue);

            const test = this.isFullMode() && pureValue != null;
            if(test){
                this.nbElementDisplayed++;
                $(".totals-tax").remove();
            }
            return test;
        },

        /**
         * Get surcharge title
         *
         * @returns {null|String}
         */
        getTitle: function (myValue) {
            if (!this.totals()) {
                return null;
            }
            if(myValue === "transiteo_duty"){
                return $t('Duty');
            }
            if(myValue === "transiteo_vat"){
                return $t('Vat/Gst');
            }
            if(myValue === "transiteo_special_taxes"){
                return $t('Special Taxes');
            }

            return myValue;

        },


        /**
         * @return {Number}
         */
        getPureValue: function (myValue) {
            if(myValue === "transiteo_duty"){
                return window.checkoutConfig.transiteo_duty;
            }
            if(myValue === "transiteo_vat"){
                return window.checkoutConfig.transiteo_vat;
            }
            if(myValue === "transiteo_special_taxes"){
                return window.checkoutConfig.transiteo_special_taxes;
            }

        },

        getIncluded: function (){
            if(window.checkoutConfig.transiteo_incoterm === 'ddp'){
                return $t('Included');
            }else{
                return $t('Not included');
            }
        },

        getIncoterm: function(){
            if(window.checkoutConfig.transiteo_incoterm === 'ddp'){
                return $t('DDP : Duty & taxes included in the order. No Taxes to pay at arrival.');
            }else{
               return  $t('DAP : Duty & Taxes not included in the order. It will have to be paid at arrival.');
            }

        },

        /**
         * @return {*|String}
         */
        getValue: function (value) {
            return this.getFormattedPrice(this.getPureValue(value));
        }
    });
});
