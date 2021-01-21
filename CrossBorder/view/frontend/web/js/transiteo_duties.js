/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

define([
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
        'use strict';
        return Component.extend({
            initialize: function () {
                this.vat = '10.00';
                this.duty = '5.00';
                this.special = '6.00';
                this._super();
            },
        });
    }
);
