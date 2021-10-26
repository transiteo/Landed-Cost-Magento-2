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
