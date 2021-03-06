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
    'Magento_Catalog/js/price-utils',
    'domReady!'
], function ($, utils) {
    'use strict';

    $.widget('mage.productCustomizablePrice', {
        options: {
            productFormSelector: '#product_addtocart_form',
            qtyFieldSelector: '#qty',
            totalTaxesPriceContainerSelector: '.price-subtotal .price-container .price-wrapper .total-taxes',
            vatPriceContainerSelector: '.price-subtotal .price-container .price-wrapper .vat',
            dutyPriceContainerSelector: '.price-subtotal .price-container .price-wrapper .duty',
            specialTaxesPriceContainerSelector: '.price-subtotal .price-container .price-wrapper .special-taxes',
            superAttributeSelector: '.super-attribute-select',
            countrySelector: 'select[name=\'country_id\']',
            eventAction: 'change input',
            requestUrl: null,
            enableLoader: true,
            delay: 60
        },
        superAttributesFields: null,
        productForm: null,
        qtyField: null,
        totalTaxesContainer: null,
        vatPriceContainer: null,
        dutyPriceContainer: null,
        specialTaxesPriceContainer: null,
        isAjaxPending: false,
        cachePrice: {},

        /**
         * @private
         */
        _create: function () {
            this.productForm = $(this.options.productFormSelector);
            this.qtyField = $(this.options.qtyFieldSelector);
            this.totalTaxesContainer = $(this.options.totalTaxesPriceContainerSelector);
            this.vatPriceContainer = $(this.options.vatPriceContainerSelector);
            this.specialTaxesPriceContainer = $(this.options.specialTaxesPriceContainerSelector);
            this.dutyPriceContainer = $(this.options.dutyPriceContainerSelector);
            this.superAttributesFields = $(this.options.superAttributeSelector);
        },

        /**
         * @private
         */
        _init: function () {
            $(this.options.countrySelector).val(null);
            this._filTaxesContainer(this._getProductQty());
            this._bind();
        },

        /**
         * @private
         */
        _bind: function () {
            var _self = this;
            this.superAttributesFields.on(this.options.eventAction, function () {
                _self._filTaxesContainer(_self._getProductQty());
            });
            let countries =  $(this.options.countrySelector);
            countries.on(this.options.eventAction, function () {
                const _current = $(this);
                countries.attr('propagateSelfChanges', true);
                countries.val(_current.val());
                countries.attr('propagateSelfChanges', null);
                $(_self.options.countrySelector).val();
                if(!_current.attr('propagate') && !_current.attr('propagateSelfChanges')){
                    _self._updateSelectedCountry();
                    _self._filTaxesContainer(_self._getProductQty());
                }
            })
            this.qtyField.on(this.options.eventAction, function () {
                if(!$(this).attr('propagate')){
                    _self._filTaxesContainer(_self._getProductQty());
                }
            })
        },

        /**
         * Retrieve the current product sku
         *
         * @return {string}
         * @private
         */
        _getProductSku: function () {
            return this.productForm.data('product-sku');
        },

        /**
         * Retrieve the current product qty
         *
         * @return {number}
         * @private
         */
        _getProductQty: function () {
            return parseFloat(this.qtyField.val());
        },

        /**
         * Update the sub total price container with tht final price
         *
         * @param {number} productQty
         * @return void
         * @private
         */
        _filTaxesContainer: function ( productQty) {
            let _self = this;
            let cacheKey = _self._getCacheKey();
            if (this.cachePrice.hasOwnProperty(cacheKey)) {
                if(_self.options.enableLoader){
                    $('body').trigger('processStart');
                }
                window.setTimeout(function () {
                    _self.totalTaxesContainer.html(_self._formatPrice(_self.cachePrice[cacheKey].total_taxes));
                    _self.vatPriceContainer.html(_self._formatPrice(_self.cachePrice[cacheKey].vat));
                    _self.dutyPriceContainer.html(_self._formatPrice(_self.cachePrice[cacheKey].duty));
                    _self.specialTaxesPriceContainer.html(_self._formatPrice(_self.cachePrice[cacheKey].special_taxes));
                    if(_self.options.enableLoader){
                        $('body').trigger('processStop');
                    }
                }, 500);


            } else {
                window.setTimeout(function () {
                    _self._updateSubTotalPriceContainer(productQty, cacheKey)
                        .done(function (data) {
                            if (data.backUrl) {
                                window.location = data.backUrl;
                            }
                            // console.log(data);
                            if (data.success) {
                                if(data.country_code){
                                    let countries = $("select[name='country_id']");
                                    countries.attr('propagate', true);
                                    countries.val(data.country_code);
                                    countries.attr('propagate', null);
                                    //update the cache key to save the value for the good country
                                    cacheKey = _self._getCacheKey();
                                }
                                _self.cachePrice[cacheKey] = Object.create(data);
                                _self.totalTaxesContainer.html(_self._formatPrice(_self.cachePrice[cacheKey].total_taxes));
                                _self.vatPriceContainer.html(_self._formatPrice(_self.cachePrice[cacheKey].vat));
                                _self.dutyPriceContainer.html(_self._formatPrice(_self.cachePrice[cacheKey].duty));
                                _self.specialTaxesPriceContainer.html(_self._formatPrice(_self.cachePrice[cacheKey].special_taxes));
                            }
                            if (data.error) {
                                console.log(data.error);
                            }
                        }).always(function () {
                        _self.isAjaxPending = false;
                        if(_self.options.enableLoader){
                            $('body').trigger('processStop');
                        }
                    });
                }, _self.options.delay);
            }
        },

        /**
         * Return price or error message
         * @param price
         * @returns string
         * @private
         */
        _formatPrice(price){
            if(price !== null){
                return String(utils.formatPrice(price));
            }else{
                //return 0 if the price wasn't estimated
                return String(utils.formatPrice(0.0));
            }
        },

        /**
         * get Cache Key
         * @returns {string}
         * @private
         */
        _getCacheKey: function () {
            let serializedArray = $('#product_addtocart_form').serializeArray();
            return String(serializedArray.map(function (elem) {
                return elem.value;
            }).join('-')) + '-' + this._getCountry();
        },

        /**
         * @returns string
         * @private
         */
        _getCountry : function (){
            return String($("select[name='country_id']").val());
        },

        /**
         *
         * Update the sub total price container
         *
         * @param {number} productQty
         * @param {string} cacheKey
         * @return {*}
         * @private
         */
        _updateSubTotalPriceContainer: function (productQty, cacheKey) {
            let _self = this;
            let data = $('#product_addtocart_form').serialize();
            //hide prices during load
            this.totalTaxesContainer.html("");
            this.vatPriceContainer.html("");
            this.dutyPriceContainer.html("");
            this.specialTaxesPriceContainer.html("");
            return $.ajax({
                url: this.options.requestUrl + '?' + data + "&country_code=" + $(_self.options.countrySelector).val(),
                type: 'GET',
                dataType: 'json',
                context: {
                    qty: productQty
                },
                beforeSend: function () {
                    if(_self.options.enableLoader){
                        $('body').trigger('processStart');
                    }
                    _self.isAjaxPending = (!_self.isAjaxPending && cacheKey === _self._getCacheKey());
                    return _self.isAjaxPending;
                }
            });
        },

        /**
         * Update the selected country
         * @private
         */
        _updateSelectedCountry: function () {
            let _self = this;
            return $.ajax({
                url: this.options.requestUrl + '?' + "country_code=" + $(_self.options.countrySelector).val() + "&form_key=" + $('input[name="form_key"]').val(),
                type: 'GET',
                dataType: 'json',
            });
        }
    });

    return $.mage.productCustomizablePrice;
});
