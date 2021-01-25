/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
        'use strict';

    // reload of geoip_country section
    var sections = ['geoip_country'];
    try{
        customerData.initStorage();
    }catch (e){
        console.log("Version of magento is not 2.4.1");
    }
    customerData.invalidate(sections);
    customerData.reload(sections, true);

        return Component.extend({
            initialize: function () {
                this.geoip_country = customerData.get('geoip_country');
                this._super();
            },
        });
    }
);
