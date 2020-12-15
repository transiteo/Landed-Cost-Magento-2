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
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Setup\Exception;
use Magento\Store\Model\StoreManagerInterface;
use Transiteo\Base\Model\TransiteoApiShipmentParameters;
use Transiteo\Base\Model\TransiteoApiShipmentParametersFactory;
use Transiteo\Base\Model\TransiteoApiSingleProductParameters;
use Transiteo\Taxes\Model\TransiteoProducts;

class TaxesService
{
    public const SHIPPING_AMOUNT = 'shipping_amount';
    public const RECEIVER_PRO = 'receiver_pro';
    public const RECEIVER_ACTIVITY = 'receiver_activity';
    public const TO_COUNTRY = 'to_country';
    public const TO_DISTRICT = 'to_district';

    public const RETURN_KEY_DUTY = 'duty';
    public const RETURN_KEY_VAT = 'vat';
    public const RETURN_KEY_SPECIAL_TAXES = 'special_taxes';
    public const RETURN_KEY_TOTAL_TAXES = 'total_taxes';
    public const COOKIE_NAME = 'transiteo-popup-info';

    protected $transiteoProducts;
    protected $shipmentParams;
    protected $productParams;
    protected $productRepository;
    protected $scopeConfig;
    protected $storeManager;
    protected $_countryFactory;
    protected $_flagManager;
    protected $cookieManager;

    /**
     * TaxesService constructor.
     * @param TransiteoProducts $transiteoProducts
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param TransiteoApiSingleProductParameters $productParams
     * @param TransiteoApiShipmentParameters $shipmentParams
     * @param ProductRepositoryInterface $productRepository
     * @param CountryFactory $countryFactory
     * @param CookieManagerInterface $cookieManager
     * @param FlagManager $flagManager
     */
    public function __construct(
        TransiteoProducts $transiteoProducts,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        TransiteoApiSingleProductParameters $productParams,
        TransiteoApiShipmentParameters $shipmentParams,
        ProductRepositoryInterface $productRepository,
        CountryFactory $countryFactory,
        CookieManagerInterface $cookieManager,
        FlagManager $flagManager
    ) {
        $this->cookieManager = $cookieManager;
        $this->transiteoProducts     = $transiteoProducts;
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
        return $this->getDutiesByProducts([$productId => $quantity]);
    }

    /**
     * @param array $products
     * @param array $params
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDutiesByProducts(array $products, $params = []): array
    {
        //SHIPMENT
        $shipmentParams = $this->shipmentParams;
        //get shipping amount
        if (array_key_exists(self::SHIPPING_AMOUNT, $params)) {
            $shippingAmount = $params[self::SHIPPING_AMOUNT];
        } else {
            $shippingAmount = 0;
        }
        //define if shipping is global or not
        if (count($products)>1) {
            $unitShipPrice = $shippingAmount;
            $shipmentParams->setShipmentType(false);
        } else {
            $unitShipPrice = 0;
            $shipmentParams->setShipmentType(true, $shippingAmount, $this->getCurrentStoreCurrency());
        }
        $shipmentParams->setLang($this->getTransiteoLang());

        $shipmentParams->setFromCountry($this->getIso3Country($this->getWebsiteCountry())); // country from website ISO3

        /** TODO add from district in config */
        $shipmentParams->setFromDistrict("FR-GES"); // district from DistrictRepository

        //GET to country and to district from params or cookie
        if (!array_key_exists(self::TO_COUNTRY, $params) || !array_key_exists(self::TO_DISTRICT, $params)) {
            $cookie = $this->cookieManager->getCookie('transiteo-popup-info', null);
            if ($cookie === null) {
                throw new Exception("Transiteo_Taxes country cookie does not exists.");
            } else {
                $cookie = explode('_', $cookie);
                $toCountry = $cookie[0];
                $toDistrict = $cookie[1];
            }
        } else {
            $toCountry = $params[self::TO_COUNTRY];
            $toDistrict = $params[self::TO_DISTRICT];
        }

        //IF country is ISO2 get ISO3 code
        if (strlen($toCountry) === 2) {
            $toCountry = $this->getIso3Country($toCountry);
        }
        $shipmentParams->setToCountry($toCountry); // country from customer attribute or cookie value
        $shipmentParams->setToDistrict($toDistrict); // district from customer attribute or cookie value

        /**
         * TODO add Sender pro in config
         */
        $shipmentParams->setSenderPro(true, 1000000, "EUR"); // true always, const
        //$shipmentParams->setSenderProRevenue(0); // need an input in admin
        //$shipmentParams->setSenderProRevenueCurrency("EUR"); // need an input in admin

        /**
         * TODO add Transport Carrier in config
         */
        $shipmentParams->setTransportCarrier(null); // in checkout only
        $shipmentParams->setTransportType(null); // in checkout only

        //GET RECEIVER PRO PARAM
        if (array_key_exists(self::RECEIVER_PRO, $params)) {
            $receiverPro = $params[self::RECEIVER_PRO];
        } else {
            $receiverPro = false;
        }

        // DEFINE RECEIVER TYPE
        if ($receiverPro) {
            if (array_key_exists(self::RECEIVER_ACTIVITY, $params)) {
                $receiverActivity = $params[self::RECEIVER_ACTIVITY];
            } else {
                throw new Exception("Receiver for transiteo taxe calculation is set to pro but activity is not set.");
            }
            $shipmentParams->setReceiverPro($receiverPro, $receiverActivity); // need an input in customer attribute
        } else {
            $shipmentParams->setReceiverPro($receiverPro); // need an input in customer attribute
        }

        ///PRODUCTS
        $productsParams = [];
        //////////////////LOGGER//////////////
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        ///////////////////////////////////////
        $weightUnit = substr($this->getWeightUnit(), 0, 2); // convert kgs to kg and lbs to lb
        $currentStoreCurrency = $this->getCurrentStoreCurrency();
        foreach ($products as $id => $qty) {
            $productParams = $this->productParams;
            $product = $this->productRepository->getById($id);
            ////////////
            $logger->info($product->getName());
            ///////////
            $productParams->setProductName($product->getName());
            $productParams->setWeight(round($product->getWeight(), 2));
            $productParams->setWeight_unit($weightUnit);
            $productParams->setQuantity($qty);
            $productParams->setUnit_price(round($product->getPrice(), 2));
            $productParams->setCurrency_unit_price($currentStoreCurrency);
            $productParams->setUnit_ship_price($unitShipPrice); // prix du shipping, 0 default
            $productsParams[$id] = $productParams;
        }

        $this->transiteoProducts->setProducts($productsParams);
        $this->transiteoProducts->setShipmentParams($shipmentParams);

        return [
            self::RETURN_KEY_DUTY          => $this->transiteoProducts->getTotalDuty(),
            self::RETURN_KEY_VAT           => $this->transiteoProducts->getTotalVat(),
            self::RETURN_KEY_SPECIAL_TAXES => $this->transiteoProducts->getTotalSpecialTaxes(),
            self::RETURN_KEY_TOTAL_TAXES   => $this->transiteoProducts->getTotalTaxes()
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

        if ($locale == "fr_") {
            return "fr";
        } elseif ($locale == "es_") {
            return "es";
        } else {
            return "en";
        }
    }

    // Get ISO3 Country Code from ISO2 Country Code
    public function getIso3Country($countryIsoCode2)
    {
        $country = $this->_countryFactory->create();
        $country->loadByCode($countryIsoCode2);
        return $country->getData('iso3_code');
    }
}
