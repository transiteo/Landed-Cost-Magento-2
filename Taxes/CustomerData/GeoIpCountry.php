<?php

namespace Transiteo\Taxes\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Transiteo\Taxes\Model\GeoIp;

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
        $this->geoIp = $geoIp;
        $this->scopeConfig = $scopeConfig;
        parent::__construct();
    }
    
    public function getSectionData() {

        $visitorCountry = $this->geoIp->getUserCountry();
        $websiteCountry = $this->getWebsiteCountry();

        $sameCountry = ($visitorCountry != $websiteCountry ? false : true);

        return [
            'visitor_country' => $visitorCountry,
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