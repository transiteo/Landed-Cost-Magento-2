<?php
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

namespace Transiteo\LandedCost\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Transiteo\LandedCost\Model\Config;
use Transiteo\LandedCost\Model\GeoIp;

/**
 * Example data source
 */
class GeoIpCountry extends \Magento\Framework\DataObject implements SectionSourceInterface
{
    /**
     * @var GeoIp
     */
    protected $geoIp;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        GeoIp $geoIp,
        Config $config
    ) {
        $this->geoIp       = $geoIp;
        $this->config = $config;
        parent::__construct();
    }

    /**
     * @return array
     * @throws \MaxMind\Db\Reader\InvalidDatabaseException
     */
    public function getSectionData()
    {

        if(!$this->config->isEnabled() || !$this->config->isGeoIpEnabled()){
            return [];
        }

        $visitorCountry = $this->geoIp->getUserCountry();
        $websiteCountry = $this->config->getWebsiteCountry();

        $sameCountry = ($visitorCountry != $websiteCountry ? false : true);

        return [
            'visitor_country'         => $visitorCountry,
            'same_country_as_website' => $sameCountry
        ];
    }
}
