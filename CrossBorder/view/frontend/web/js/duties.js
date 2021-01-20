define([
    'jquery',
    'Magento_Catalog/js/price-utils'
], function ($, utils) {
    'use strict'

    return function (config, element) {
        var productId = $(element).parents('.price-box').data('product-id'),
            priceFormat = (config.priceConfig && config.priceConfig.priceFormat) || {}

        if (productId) {
            var url = '/transiteo/product/taxes/id/' + productId

            $.ajax({
                url: url,
                type: 'POST',
            }).done(function (data) {
                var vatPrice = null,
                    specialTaxesPrice = null,
                    dutiesPrice = null,
                    showDiv = false;

                if (data.vat) {
                    vatPrice = utils.formatPrice(data.vat, priceFormat);
                    $(element).find('.vat-price').html(vatPrice);
                    $(element).find('.vat').show();
                    showDiv = true;
                }

                if (data.special_taxes) {
                    specialTaxesPrice = utils.formatPrice(data.special_taxes, priceFormat);
                    $(element).find('.special-price').html(specialTaxesPrice);
                    $(element).find('.special').show();
                    showDiv = true;
                }

                if (data.duty) {
                    dutiesPrice = utils.formatPrice(data.duty, priceFormat);
                    $(element).find('.duty-price').html(dutiesPrice);
                    $(element).find('.duty').show();
                    showDiv = true;
                }

                if (showDiv) {
                    $(element).show();
                }
            })
        }
    }
})
