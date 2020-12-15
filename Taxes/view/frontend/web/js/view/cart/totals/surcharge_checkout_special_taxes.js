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
            return this.isFullMode() && this.getPureValue() != 0;
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
            return window.checkoutConfig.transiteo_special_taxes;
        },

        /**
         * @return {*|String}
         */
        getValue: function () {
            return this.getFormattedPrice(this.getPureValue());
        }
    });
});
