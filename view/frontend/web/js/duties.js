/*
 * Transiteo LandedCost
 *
 * NOTICE OF LICENSE
 * if you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@bird.eu so we can send you a copy immediately.
 * @category      Transiteo
 * @package       Transiteo_LandedCost
 * @copyright    Open Software License (OSL 3.0)
 * @author          Blackbird Team
 * @license          MIT
 * @support        https://github.com/transiteo/Landed-Cost-Magento-2/issues/new/
 */

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
