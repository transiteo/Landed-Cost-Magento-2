define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'jquery',
    'mage/translate'
], function (Component, quote, $, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Transiteo_Taxes/summary/surcharge_checkout_vat'
        },
        totals: quote.getTotals(),

        /**
         * @return {*|Boolean}
         */
        isDisplayed: function () {
            return this.isFullMode() && this.getPureValue() != null;
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

            return $t('Vat/Gst');
        },

        /**
         * @return {Number}
         */
        getPureValue: function () {
            return window.checkoutConfig.transiteo_vat;
        },

        /**
         * @return {*|String}
         */
        getValue: function () {
            return this.getFormattedPrice(this.getPureValue());
        }
    });
});
