<?php
/**
 * @author Joris HART <jhart@efaktory.fr>
 * @copyright Copyright (c) 2020 eFaktory (https://www.efaktory.fr)
 * @link https://www.efaktory.fr
 */
declare(strict_types=1);

namespace Transiteo\Taxes\Service;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\FlagManager;
use Magento\Store\Model\StoreManagerInterface;
use Transiteo\Base\Model\TransiteoApiShipmentParameters;
use Transiteo\Base\Model\TransiteoApiSingleProductParameters;
use Transiteo\Taxes\Model\TransiteoSingleProduct;

class TaxesService
{
    protected $singleProduct;
    protected $shipmentParams;
    protected $productParams;
    protected $productRepository;
    protected $scopeConfig;
    protected $storeManager;
    protected $_countryFactory;
    protected $_flagManager;

    public function __construct(
        TransiteoSingleProduct $singleProduct,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        TransiteoApiSingleProductParameters $productParams,
        TransiteoApiShipmentParameters $shipmentParams,
        ProductRepositoryInterface $productRepository,
        CountryFactory $countryFactory,
        FlagManager $flagManager
    ) {
        $this->singleProduct     = $singleProduct;
        $this->shipmentParams    = $shipmentParams;
        $this->productParams     = $productParams;
        $this->productRepository = $productRepository;
        $this->scopeConfig       = $scopeConfig;
        $this->storeManager      = $storeManager;
        $this->_countryFactory = $countryFactory;
        $this->_flagManager = $flagManager;
        }

    /**
     * @param int $productId
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDutiesByProductId(int $productId, int $quantity = 1): array
    {
        $product = $this->productRepository->getById($productId);

        $this->productParams->setProductName($product->getName());
        $this->productParams->setWeight(round($product->getWeight(),2));
        $this->productParams->setWeight_unit(substr($this->getWeightUnit(), 0, 2)); // convert kgs to kg and lbs to lb
        $this->productParams->setQuantity($quantity);
        $this->productParams->setUnit_price(round($product->getPrice(),2));
        $this->productParams->setCurrency_unit_price($this->getCurrentStoreCurrency());

       

        /**
         * @todo Which value has to be filed in here ??
         */
        $this->shipmentParams->setShipmentType("ARTICLE"); // ARTICLE or GLOBAL if multiple articless
        $this->productParams->setUnit_ship_price(0); // prix du shipping, 0 default

        $this->shipmentParams->setLang($this->getTransiteoLang());

        $this->shipmentParams->setFromCountry($this->getIso3Country($this->getWebsiteCountry())); // country from website ISO3

        $this->shipmentParams->setFromDistrict("FR-GES"); // district from DistrictRepository
        $this->shipmentParams->setToCountry("USA"); // country from customer attribute or cookie value
        $this->shipmentParams->setToDistrict("US-MO-65055"); // district from customer attribute or cookie value

        $this->shipmentParams->setSenderPro(true); // true always, const
        $this->shipmentParams->setSenderProRevenue(0); // need an input in admin
        $this->shipmentParams->setSenderProRevenueCurrency("EUR"); // need an input in admin

        $this->shipmentParams->setTransportCarrier(null); // in checkout only
        $this->shipmentParams->setTransportType(null); // in checkout only

        $this->shipmentParams->setReceiverPro(false); // need an input in customer attribute
        $this->shipmentParams->setReceiverActivity(null); // need an input in customer attribute, required if pro = true




        $this->singleProduct->setParams($this->productParams);
        $this->singleProduct->setShipmentParams($this->shipmentParams);

        return [
            "duty"          => $this->singleProduct->getDuty(),
            "vat"           => $this->singleProduct->getVat(),
            "special_taxes" => $this->singleProduct->getSpecialTaxes(),
            "total"         => $this->singleProduct->getTotalTaxes()
        ];
    }

    /**
     * @return mixed
     */
    private function getWeightUnit()
    {
        return $this->scopeConfig->getValue(
            'general/locale/weight_unit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCurrentStoreCurrency()
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
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
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

     /**
     * Get Locale Code
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->scopeConfig->getValue(
            'general/locale/code',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get transiteo lang Code
     *
     * @return string
     */
    public function getTransiteoLang(): string
    {
        $locale = substr($this->getLocale(), 0, 3);

        if($locale == "fr_")
            return "fr";
        elseif($locale == "es_")
            return "es";
        else
            return "en";

    }

    // Get ISO3 Country Code from ISO2 Country Code
    public function getIso3Country($countryIsoCode2){    
        $country = $this->_countryFactory->create()->load->loadByCode($countryIsoCode2);
        return $country->getData('iso3_code');
    }

}
