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
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Transiteo\LandedCost\Model\GeoIp;

/**
 * Example data source
 */
class GeoIpCountry extends \Magento\Framework\DataObject implements SectionSourceInterface
{
    protected $geoIp;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        GeoIp $geoIp,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->geoIp       = $geoIp;
        $this->scopeConfig = $scopeConfig;
        parent::__construct();
    }

    /**
     * @return array
     * @throws \MaxMind\Db\Reader\InvalidDatabaseException
     */
    public function getSectionData()
    {
        $visitorCountry = $this->geoIp->getUserCountry();
        $websiteCountry = $this->getWebsiteCountry();

        $sameCountry = ($visitorCountry != $websiteCountry ? false : true);

        return [
            'visitor_country'         => $visitorCountry,
            'same_country_as_website' => $sameCountry
        ];
    }

    /**
     * Get Country code by website scope
     *
     * @return string
     */
    public function getWebsiteCountry(): string
    {
        return $this->scopeConfig->getValue(
            'general/country/default',
            ScopeInterface::SCOPE_WEBSITES
        );
    }
}
