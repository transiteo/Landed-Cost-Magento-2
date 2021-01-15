define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'jquery',
    'mage/translate'
], function (Component, quote, $, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Transiteo_Taxes/summary/surcharge_checkout_special_taxes'
        },
        totals: quote.getTotals(),

        /**
         * @return {*|Boolean}
         */
        isDisplayed: function () {
            //get pure value
            const pureValue = this.getPureValue();

            const test = this.isFullMode() && pureValue != null;
            if(test){
                //change value
                const price = document.getElementById('transiteo_special_taxes_amount');
                if(price){
                    price.innerHTML = this.getFormattedPrice(pureValue);
                }

                $(".totals-tax").remove();
            }
            return test;
        },

        /**
         * Get surcharge title
         *
         * @returns {null|String}
         */
        getTitle: function () {
            if (!this.totals()) {
                return null;
            }

            return $t('Special Taxes');
        },

        /**
         * @return {Number}
         */
        getPureValue: function () {
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
            return window.checkoutConfig.transiteo_special_taxes;
        },

        getIncluded: function (){
            if(window.checkoutConfig.transiteo_incoterm === 'ddp'){
                return $t('(Included)');
            }else{
                return $t('(Not included)');
            }
        },

        /**
         * @return {*|String}
         */
        getValue: function () {
            return this.getFormattedPrice(this.getPureValue());
        }
    });
});
