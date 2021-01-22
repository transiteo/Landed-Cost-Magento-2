<?php
/*
 * @copyright Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Transiteo\CrossBorder\Service;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\FlagManager;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Store\Model\StoreManagerInterface;
use Transiteo\CrossBorder\Logger\Logger;
use Transiteo\CrossBorder\Model\TransiteoApiProductParametersFactory;
use Transiteo\CrossBorder\Model\TransiteoApiShipmentParameters;
use Transiteo\CrossBorder\Model\TransiteoProducts;

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
    protected $regionFactory;
    protected $_flagManager;
    protected $cookieManager;
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * TaxesService constructor.
     * @param TransiteoProducts $transiteoProducts
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param TransiteoApiProductParametersFactory $productParamsFactory
     * @param TransiteoApiShipmentParameters $shipmentParams
     * @param CollectionFactory $productCollectionFactory
     * @param CountryFactory $countryFactory
     * @param RegionFactory $regionFactory
     * @param CookieManagerInterface $cookieManager
     * @param FlagManager $flagManager
     * @param Logger $logger
     */
    public function __construct(
        TransiteoProducts $transiteoProducts,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        TransiteoApiProductParametersFactory $productParamsFactory,
        TransiteoApiShipmentParameters $shipmentParams,
        CollectionFactory $productCollectionFactory,
        CountryFactory $countryFactory,
        RegionFactory $regionFactory,
        CookieManagerInterface $cookieManager,
        FlagManager $flagManager,
        Logger $logger
    ) {
        $this->logger = $logger;
        $this->regionFactory = $regionFactory;
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

    /**
     * @param array $products array of quote items
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
            $globalShipPrice = 0;
            $shipmentParams->setShipmentType(true, round($shippingAmount, 2), $this->getCurrentStoreCurrency());
        } else {
            $globalShipPrice = $shippingAmount;
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
                throw new \Exception("Transiteo_CrossBorder getting country from cookie is disallowed.");
            }
            $cookie = $this->cookieManager->getCookie('transiteo-popup-info', null);
            if ($cookie === null) {
                throw new \Exception("Transiteo_CrossBorder country cookie does not exists.");
            }

            $cookie = explode('_', $cookie);
            $toCountry = $cookie[0];
            if (!array_key_exists(self::TO_DISTRICT, $params) || $params[self::TO_DISTRICT] === "") {
                $toDistrict = $cookie[1];
            } else {
                $toDistrict = $params[self::TO_DISTRICT];
            }
        } else {
            $toCountry = $params[self::TO_COUNTRY];
            if (array_key_exists(self::TO_DISTRICT, $params)) {
                $toDistrict = $params[self::TO_DISTRICT];
            } else {
                //Set to district = ""
                $toDistrict = "";
                //set default district for usa, and Brazil and Canada.
                if ($toCountry === "USA") {
                    $toDistrict = "US-CA-90034";
                }
                if ($toCountry === "CAN") {
                    $toDistrict = "CA-AB";
                }
                if ($toCountry === "BRA") {
                    $toDistrict = "BR-AC";
                }
            }
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
                throw new \Exception("Receiver for transiteo taxe calculation is set to pro but activity is not set.");
            }
            $shipmentParams->setReceiverPro($receiverPro, $receiverActivity); // need an input in customer attribute
        } else {
            $shipmentParams->setReceiverPro($receiverPro); // need an input in customer attribute
        }

        ///PRODUCTS
        $productsParams = [];
        $weightUnit = $this->getWeightUnit();
        $currentStoreCurrency = $this->getCurrentStoreCurrency();

        foreach ($products as $quoteItem) {
            $qty = $quoteItem->getQty();
            $product = $quoteItem->getProduct();
            $productParams = $this->productParamsFactory->create();
            $id = $product->getId();
            $productParams->setProductName($product->getName());
            $productParams->setWeight(round($product->getWeight(), 2));
            $productParams->setWeight(0);
            $productParams->setWeight_unit($weightUnit);
            $productParams->setQuantity($qty);
            $productParams->setUnit_price(round($product->getPrice() * $this->getCurrentCurrencyRate(), 2));
            $productParams->setCurrency_unit_price($currentStoreCurrency);
            if ($globalShipPrice === 0) {
                $productParams->setUnit_ship_price(0); // 0 default
            } else {
                $productParams->setUnit_ship_price(round($globalShipPrice/$qty, 2)); // prix du shipping
            }
            $productsParams[$id] = $productParams;
        }

        $this->transiteoProducts->setProducts($productsParams);
        $this->transiteoProducts->setShipmentParams($shipmentParams);

        foreach ($products as $quoteItem) {
            $product = $quoteItem->getProduct();
            /**
             * @var CartItemInterface $product
             */
            $id = $product->getId();

            $currencyRate = $this->getCurrentCurrencyRate();
            $duty = $this->transiteoProducts->getDuty($id);
            $specialTaxes = $this->transiteoProducts->getSpecialTaxes($id);
            $totalTaxes = $this->transiteoProducts->getTotalTaxes($id);
            $vatAmount = $this->transiteoProducts->getVat($id);

            //Set Transiteo Taxes
            $quoteItem->setData('transiteo_vat', $vatAmount);
            $quoteItem->setData('transiteo_duty', $duty);
            $quoteItem->setData('transiteo_special_taxes', $specialTaxes);
            $quoteItem->setData('transiteo_total_taxes', $totalTaxes);

            if (isset($vatAmount)) {
                $quoteItem->setData('base_transiteo_vat', $vatAmount / $currencyRate);
            } else {
                $quoteItem->setData('base_transiteo_vat', null);
            }

            if (isset($duty)) {
                $quoteItem->setData('base_transiteo_duty', $duty / $currencyRate);
            } else {
                $quoteItem->setData('base_transiteo_duty', null);
            }

            if (isset($specialTaxes)) {
                $quoteItem->setData('base_transiteo_special_taxes', $specialTaxes / $currencyRate);
            } else {
                $quoteItem->setData('base_transiteo_special_taxes', null);
            }
            if (isset($totalTaxes)) {
                $quoteItem->setData('base_transiteo_total_taxes', $totalTaxes / $currencyRate);
            } else {
                $quoteItem->setData('base_transiteo_total_taxes', null);
            }

            //Set Tax Amount if incoterm is ddp
            if ($this->isDDPActivated()) {
                $quoteItem->setTaxAmount($totalTaxes);
                $quoteItem->setBaseTaxAmount($totalTaxes / $currencyRate);
                $quoteItem->setTaxPercent(0);
            }
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
            'shipping/origin/country_id',
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
        $country = $this->getWebsiteCountry();
        $regionId = $this->scopeConfig->getValue(
            'shipping/origin/region_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
        $region =  $this->regionFactory->create()->load($regionId)->getCode();
        $zip = $this->scopeConfig->getValue(
            'shipping/origin/postcode',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
        $r = $country . '-' . $region;
        if ($country === "US") {
            $r .= '-' . $zip;
        }
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
    public function getIncoterm()
    {
        return $this->scopeConfig->getValue(
            'transiteo_settings/duties/incoterm',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
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
            if ($value === 'checkout') {
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
            if ($value === 'cart') {
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

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
