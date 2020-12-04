<?php

declare(strict_types=1);

namespace Transiteo\Taxes\Controller\Test;

use Transiteo\Taxes\Model\GeoIp;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\ScopeInterface;

class View extends \Magento\Framework\App\Action\Action{

    protected $geoIp;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        GeoIp $geoIp,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->geoIp = $geoIp;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function execute(){

        $visitorCountry = $this->geoIp->getUserCountry();
        $websiteCountry = $this->getWebsiteCountry();

        $sameCountry = ($visitorCountry != $websiteCountry ? false : true);

        /** @var Json $jsonResult */
        $jsonResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $jsonResult->setData([
            'same_country' => $sameCountry,
            'visitor_country' => $visitorCountry,
            'website_country' => $websiteCountry,
        ]);

        return $jsonResult;
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
