<?php
/**
 * @author Joris HART <jhart@efaktory.fr>
 * @copyright Copyright (c) 2020 eFaktory (https://www.efaktory.fr)
 * @link https://www.efaktory.fr
 */
declare(strict_types=1);

namespace Transiteo\Taxes\Service;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\FlagManager;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Setup\Exception;
use Magento\Store\Model\StoreManagerInterface;
use Transiteo\Base\Model\TransiteoApiShipmentParameters;
use Transiteo\Base\Model\TransiteoApiSingleProductParametersFactory;
use Transiteo\Taxes\Model\TransiteoProducts;

class TaxesService
{
    public const SHIPPING_AMOUNT = 'shipping_amount';
    public const RECEIVER_PRO = 'receiver_pro';
    public const RECEIVER_ACTIVITY = 'receiver_activity';
    public const TO_COUNTRY = 'to_country';
    public const TO_DISTRICT = 'to_district';
    public const DISALLOW_GET_COUNTRY_FROM_COOKIE = 'disallow_get_country_from_cookie';

    public const RETURN_KEY_DUTY = 'duty';
    public const RETURN_KEY_VAT = 'vat';
    public const RETURN_KEY_SPECIAL_TAXES = 'special_taxes';
    public const RETURN_KEY_TOTAL_TAXES = 'total_taxes';
    public const COOKIE_NAME = 'transiteo-popup-info';

    protected $transiteoProducts;
    protected $shipmentParams;
    protected $productParamsFactory;
    protected $productCollectionFactory;
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
     * @param TransiteoApiSingleProductParametersFactory $productParamsFactory
     * @param TransiteoApiShipmentParameters $shipmentParams
     * @param CollectionFactory $productCollectionFactory
     * @param CountryFactory $countryFactory
     * @param CookieManagerInterface $cookieManager
     * @param FlagManager $flagManager
     */
    public function __construct(
        TransiteoProducts $transiteoProducts,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        TransiteoApiSingleProductParametersFactory $productParamsFactory,
        TransiteoApiShipmentParameters $shipmentParams,
        CollectionFactory $productCollectionFactory,
        CountryFactory $countryFactory,
        CookieManagerInterface $cookieManager,
        FlagManager $flagManager
    ) {
        $this->cookieManager = $cookieManager;
        $this->transiteoProducts     = $transiteoProducts;
        $this->shipmentParams    = $shipmentParams;
        $this->productParamsFactory     = $productParamsFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->scopeConfig       = $scopeConfig;
        $this->storeManager      = $storeManager;
        $this->_countryFactory = $countryFactory;
        $this->_flagManager = $flagManager;
    }

//    /**
//     * @param int $productId
//     *
//     * @return array
//     * @throws \Magento\Framework\Exception\NoSuchEntityException
//     */
//    public function getDutiesByProductId(int $productId, int $quantity = 1): array
//    {
//        return $this->getDutiesByProducts([$productId => $quantity]);
//    }

    /**
     * @param array $products composed of row ['qty' => $qty, 'product' => $product];
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
            $unitShipPrice = 0;
            $shipmentParams->setShipmentType(true, round($shippingAmount, 2), $this->getCurrentStoreCurrency());
        } else {
            $unitShipPrice = $shippingAmount;
            $shipmentParams->setShipmentType(false);
        }
        $shipmentParams->setLang($this->getTransiteoLang());

        $shipmentParams->setFromCountry($this->getIso3Country($this->getWebsiteCountry())); // country from website ISO3

        /** TODO add from district in config */
        $shipmentParams->setFromDistrict($this->getWebsiteDistrict()); // district from DistrictRepository

        //GET to country and to district from params or cookie
        if ((!array_key_exists(self::TO_COUNTRY, $params))) {
            if ((array_key_exists(self::DISALLOW_GET_COUNTRY_FROM_COOKIE, $params)
                && $params[self::DISALLOW_GET_COUNTRY_FROM_COOKIE])) {
                throw new Exception("Transiteo_Taxes getting country from cookie is disallowed.");
            }
            $cookie = $this->cookieManager->getCookie('transiteo-popup-info', null);
            if ($cookie === null) {
                throw new Exception("Transiteo_Taxes country cookie does not exists.");
            } else {
                $cookie = explode('_', $cookie);
                $toCountry = $cookie[0];
                if (!array_key_exists(self::TO_DISTRICT, $params) || $params[self::TO_DISTRICT] === "") {
                    $toDistrict = $cookie[1];
                } else {
                    $toDistrict = $params[self::TO_DISTRICT];
                }
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
        $shipmentParams->setSenderPro(true, 1, "EUR"); // true always, const
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
        $weightUnit = $this->getWeightUnit();
        $currentStoreCurrency = $this->getCurrentStoreCurrency();
//        $productCollection = $this->productCollectionFactory->create()
//            ->addAttributeToSelect('*')
//            ->addFieldToFilter('entity_id', ['in' => array_keys($products)])
//            ->load();

        foreach ($products as $row) {
            $qty = $row['qty'];
            $product = $row['product'];
            $productParams = $this->productParamsFactory->create();
            /**
             * @var \Magento\Quote\Api\Data\CartItemInterface $product
             */
            $id = $product->getId();
//            $qty = $products[$id];
            ////////////
            $logger->info($product->getName());
            ///////////
            $productParams->setProductName($product->getName());
            $logger->info("Weight : " . $product->getWeight() . " -> " . round($product->getWeight(), 2));
            //$productParams->setWeight(1);
            $productParams->setWeight(round($product->getWeight(), 2));
            $productParams->setWeight_unit($weightUnit);
            $productParams->setQuantity($qty);
            $productParams->setUnit_price(round($product->getPrice() * $this->getCurrentCurrencyRate(), 2));
            $productParams->setCurrency_unit_price($currentStoreCurrency);
            $productParams->setUnit_ship_price(round($unitShipPrice, 2)); // prix du shipping, 0 default
            $productsParams[$id] = $productParams;
        }

        $this->transiteoProducts->setProducts($productsParams);
        $this->transiteoProducts->setShipmentParams($shipmentParams);

        foreach ($products as $row) {
            $qty = $row['qty'];
            $product = $row['product'];
            /**
             * @var CartItemInterface $product
             */
            $id = $product->getId();
            $product->setTransiteoVat($this->transiteoProducts->getVat($id));
            $product->setTransiteoDuty($this->transiteoProducts->getDuty($id));
            $product->setTransiteoSpecialTaxes($this->transiteoProducts->getSpecialTaxes($id));
            $taxeAmount = $this->transiteoProducts->getTotalTaxes($id);
            $product->setTransiteoTotalTaxes($taxeAmount);
            $product->setTaxAmount($taxeAmount);
            //////////////////LOGGER//////////////
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/saveQuotItemTaxes.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('Product ' . $product->getName()
                . ' vat ' . $product->getTransiteoVat()
                . ' duty ' . $product->getTransiteoDuty()
                . ' special taxes  ' . $product->getTransiteoSpecialTaxes()
                . ' Total Taxes ' . $product->getTransiteoTotalTaxes());
            ///////////////////////////////////////
//            $product->setTaxPercent($this->transiteoProducts->getVatPercentage($id));
        }

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
        $unit = $this->scopeConfig->getValue(
            'general/locale/weight_unit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($unit === "kgs") {
            return "kg";
        }
        if ($unit === "lbs") {
            return "lb";
        }
        return $unit;
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
     * Get Website District code by website scope
     *
     * @return string
     */
    public function getWebsiteDistrict(): string
    {
        $r = $this->scopeConfig->getValue(
            'general/country/district',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
        if ($r === null) {
            $r = "";
        }
        //////////////////LOGGER//////////////
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/district.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('District : = ' . $r);
        ///////////////////////////////////////
        return $r;
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

        if ($locale === "fr_") {
            return "fr";
        } elseif ($locale === "es_") {
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

    /**
     *  Return if ddp is activate.
     *
     * @return bool|mixed
     */
    public function isDDPActivated()
    {
        if ($this->scopeConfig->getValue(
            'transiteo_settings/duties/incoterm',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) === 'ddp') {
            return true;
        }

        return false;
    }

    /**
     * Return true if is activated on checkout
     *
     * @return bool
     */
    public function isActivatedOnCheckout()
    {
        $values = explode(',', $this->scopeConfig->getValue(
            'transiteo_settings/duties/enabled_on',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
        foreach ($values as $value) {
            if ($value = 'checkout') {
                return true;
            }
        }
        return false;
    }

    /**
     * Return true if is activated on cart view
     *
     * @return bool
     */
    public function isActivatedOnCartView()
    {
        $values = explode(',', $this->scopeConfig->getValue(
            'transiteo_settings/duties/enabled_on',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
        foreach ($values as $value) {
            if ($value = 'cart') {
                return true;
            }
        }
        return false;
    }

    /**
     * @return StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentCurrencyRate()
    {
        return $this->storeManager->getStore()->getCurrentCurrencyRate();
    }
}
