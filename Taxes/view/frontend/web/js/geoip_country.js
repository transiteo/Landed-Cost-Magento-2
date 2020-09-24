define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
        'use strict';

    // reload of geoip_country section
    var sections = ['geoip_country'];
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