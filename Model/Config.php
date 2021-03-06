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


declare(strict_types=1);

namespace Transiteo\LandedCost\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const COOKIE_NAME = 'transiteo-popup-info';
    public const CONFIG_PATH_PDP_LOADER_ENABLED = 'transiteo_landedcost_settings/pdp_settings/enable_loader';
    public const CONFIG_PATH_PDP_PRODUCT_FORM_SELECTOR = 'transiteo_landedcost_settings/pdp_settings/product_form_selector';
    public const CONFIG_PATH_PDP_QTY_FIELD_SELECTOR = 'transiteo_landedcost_settings/pdp_settings/qty_field_selector';
    public const CONFIG_PATH_PDP_TOTAL_TAXES_CONTAINER_SELECTOR = 'transiteo_landedcost_settings/pdp_settings/total_taxes_container_selector';
    public const CONFIG_PATH_PDP_VAT_CONTAINER_SELECTOR = 'transiteo_landedcost_settings/pdp_settings/vat_container_selector';
    public const CONFIG_PATH_PDP_DUTY_CONTAINER_SELECTOR = 'transiteo_landedcost_settings/pdp_settings/duty_container_selector';
    public const CONFIG_PATH_PDP_SPECIAL_TAXES_CONTAINER_SELECTOR = 'transiteo_landedcost_settings/pdp_settings/special_taxes_container_selector';
    public const CONFIG_PATH_PDP_COUNTRY_SELECTOR = 'transiteo_landedcost_settings/pdp_settings/country_selector';
    public const CONFIG_PATH_PDP_SUPER_ATTRIBUTE_SELECTOR = 'transiteo_landedcost_settings/pdp_settings/super_attribute_selector';
    public const CONFIG_PATH_PDP_EVENT_ACTION = 'transiteo_landedcost_settings/pdp_settings/event_action';
    public const CONFIG_PATH_PDP_DELAY = 'transiteo_landedcost_settings/pdp_settings/delay';
    public const CONFIG_PATH_ORDER_IDENTIFIER = 'transiteo_activation/order_sync/order_id';
    public const CONFIG_PATH_ORDER_STATUS_CORRESPONDENCE = 'transiteo_activation/order_sync/status';
    public const CONFIG_PATH_TAX_CALCULATION_METHOD = 'transiteo_activation/duties/taxes_calculation_method';
    public const CONFIG_PATH_PRICE_INCLUDES_TAXES = 'tax/calculation/price_includes_tax';
    public const CONFIG_PATH_DUTIES_ENABLED_ON = 'transiteo_activation/duties/enabled_on';
    public const CONFIG_PATH_GEOIP_DOWNLOADER_ENABLED = 'transiteo_activation/geoip/enable_geoip_download';
    public const CONFIG_PATH_GEOIP_LICENCE_KEY = 'transiteo_activation/geoip/key';
    public const CONFIG_PATH_GEOIP_CRON = 'transiteo_activation/geoip/cron';
    public const CONFIG_PATH_TRANSITEO_CLIENT_ID = 'transiteo_activation/general/client_id';
    public const CONFIG_PATH_TRANSITEO_REFRESH_TOKEN = 'transiteo_activation/general/refresh_token';

    public const TRANSITEO_ORDER_STATUS = [
        'PAID',
        'AWAITING',
        'CANCELLED'
    ];

    public const TRANSITEO_DEFAULT_STATUS = 'PAID';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var RegionFactory
     */
    protected $regionFactory;
    /**
     * @var CountryFactory
     */
    protected $countryFactory;
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param RegionFactory $regionFactory
     * @param CountryFactory $countryFactory
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RegionFactory $regionFactory,
        CountryFactory $countryFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->regionFactory = $regionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->countryFactory = $countryFactory;
        $this->encryptor = $encryptor;
    }


    /**
     * @return string
     */
    public function getTransiteoClientId():string
    {
        return (string) $this->encryptor->decrypt($this->scopeConfig->getValue(self::CONFIG_PATH_TRANSITEO_CLIENT_ID, ScopeInterface::SCOPE_STORE));
    }

    /**
     * @return string
     */
    public function getTransiteoRefreshToken():string
    {
        return (string) $this->encryptor->decrypt($this->scopeConfig->getValue(self::CONFIG_PATH_TRANSITEO_REFRESH_TOKEN, ScopeInterface::SCOPE_STORE));
    }

    /**
     * @return bool
     */
    public function isGeoIpEnabled():bool
    {
        return (bool) $this->scopeConfig->getValue(self::CONFIG_PATH_GEOIP_DOWNLOADER_ENABLED);
    }

    /**
     * @return string
     */
    public function getGeoIpLicenseKey():string
    {
        return (string) $this->encryptor->decrypt($this->scopeConfig->getValue(self::CONFIG_PATH_GEOIP_LICENCE_KEY) ?? '');
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
            ScopeInterface::SCOPE_STORE
        );
        $region = $this->regionFactory->create()->load($regionId)->getCode();
        $zip = $this->scopeConfig->getValue(
            'shipping/origin/postcode',
            ScopeInterface::SCOPE_STORE
        );
        $r = $country . '-' . $region;
        if ($country === "US") {
            $r .= '-' . $zip;
        }
        return $r;
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
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getWeightUnit(int $storeId = null): string
    {
        $unit = $this->scopeConfig->getValue(
            'general/locale/weight_unit',
            ScopeInterface::SCOPE_STORE,
            $storeId
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
     * Return true if is activated on cart view
     *
     * @return bool
     */
    public function isActivatedOnCartView(): bool
    {
        $values = explode(',', $this->scopeConfig->getValue(
            self::CONFIG_PATH_DUTIES_ENABLED_ON,
            ScopeInterface::SCOPE_STORE
        ));
        foreach ($values as $value) {
            if ($value === 'cart') {
                return true;
            }
        }
        return false;
    }

    /**
     * Return true if is activated on checkout
     *
     * @return bool
     */
    public function isActivatedOnCheckout(): bool
    {
        $values = explode(',', $this->scopeConfig->getValue(
            self::CONFIG_PATH_DUTIES_ENABLED_ON,
            ScopeInterface::SCOPE_STORE
        ));
        foreach ($values as $value) {
            if ($value === 'checkout') {
                return true;
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public function getProductIdentifier(): string
    {
        return (string)$this->scopeConfig->getValue(
            'transiteo_activation/order_sync/product_identifier',
            ScopeInterface::SCOPE_STORE
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
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     *  Return if ddp is activate.
     *
     * @return bool|mixed
     */
    public function isDDPActivated()
    {
        return $this->scopeConfig->getValue(
                'transiteo_activation/duties/incoterm',
                ScopeInterface::SCOPE_STORE
            ) === 'ddp';
    }

    /**
     *  Return if ddp is activate.
     *
     * @return bool|mixed
     */
    public function getIncoterm()
    {
        return $this->scopeConfig->getValue(
            'transiteo_activation/duties/incoterm',
            ScopeInterface::SCOPE_STORE
        );
    }


    /**
     * Return true if is activated on cart view
     *
     * @return bool
     */
    public function isActivatedOnProductPage(): bool
    {
        $values = explode(',', $this->scopeConfig->getValue(
            self::CONFIG_PATH_DUTIES_ENABLED_ON,
            ScopeInterface::SCOPE_STORE
        ));
        foreach ($values as $value) {
            if ($value === 'pdp') {
                return true;
            }
        }
        return false;
    }


    /**
     * @return bool
     */
    public function isPDPPageLoaderEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_PATH_PDP_LOADER_ENABLED, ScopeInterface::SCOPE_STORE);
    }


    /**
     * @return string
     */
    public function getPDPProductFormSelector(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_PATH_PDP_PRODUCT_FORM_SELECTOR,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the quantity field selector
     *
     * @return string
     */
    public function  getPDPQtyFieldSelector(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::CONFIG_PATH_PDP_QTY_FIELD_SELECTOR,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the sub-total container selector
     *
     * @return string
     */
    public function getPDPTotalTaxesContainerSelector(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::CONFIG_PATH_PDP_TOTAL_TAXES_CONTAINER_SELECTOR,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the sub-total container selector
     *
     * @return string
     */
    public function getPDPVatContainerSelector(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::CONFIG_PATH_PDP_VAT_CONTAINER_SELECTOR,
            ScopeInterface::SCOPE_STORE
        );
    }


    /**
     * Retrieve the sub-total container selector
     *
     * @return string
     */
    public function getPDPDutyContainerSelector(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::CONFIG_PATH_PDP_DUTY_CONTAINER_SELECTOR,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the sub-total container selector
     *
     * @return string
     */
    public function getPDPSpecialTaxesContainerSelector(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::CONFIG_PATH_PDP_SPECIAL_TAXES_CONTAINER_SELECTOR,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the sub-total container selector
     *
     * @return string
     */
    public function getPDPSuperAttributeSelector(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::CONFIG_PATH_PDP_SUPER_ATTRIBUTE_SELECTOR,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve the sub-total container selector
     *
     * @return string
     */
    public function getPDPCountrySelector(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::CONFIG_PATH_PDP_COUNTRY_SELECTOR,
            ScopeInterface::SCOPE_STORE
        );
    }


    /**
     * Retrieve the event action
     *
     * @return string
     */
    public function getPDPEventAction(): string
    {
        return (string) $this->scopeConfig->getValue(self::CONFIG_PATH_PDP_EVENT_ACTION, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve the delay sync
     *
     * @return int
     */
    public function getPDPDelay(): int
    {
        return (int) $this->scopeConfig->getValue(self::CONFIG_PATH_PDP_DELAY, ScopeInterface::SCOPE_STORE);
    }

    // Get ISO3 Country Code from ISO2 Country Code
    public function getIso3Country($countryIsoCode2)
    {
        $country = $this->countryFactory->create();
        $country->loadByCode($countryIsoCode2);
        return $country->getData('iso3_code');
    }

    // Get Country from ISO2 Country Code
    public function getCountryByCode($countryIsoCode2):\Magento\Directory\Model\Country
    {
        $country = $this->countryFactory->create();
        return $country->loadByCode($countryIsoCode2);
    }

    /**
     * @return CountryFactory
     */
    public function getCountryFactory(): CountryFactory
    {
        return $this->countryFactory;
    }


    /**
     * @return string
     */
    public function getOrderIdentifier(): string
    {
        return (string) $this->scopeConfig->getValue(self::CONFIG_PATH_ORDER_IDENTIFIER, ScopeInterface::SCOPE_STORE);
    }


    /**
     * @return string
     */
    public function getStatusCorrespondences(): array
    {
        $value = $this->scopeConfig->getValue(self::CONFIG_PATH_ORDER_STATUS_CORRESPONDENCE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if(!is_array($value)){
            $value = (array)json_decode($value ?? "", true);
            $value = array_map(function($v){
                return (array)$v;
            },$value);
        }
        return array_combine(array_column($value, 'magento_status'), array_column($value, 'transiteo_status'));
    }

    /**
     * @param ExtensibleDataInterface $product
     * @param int|null $storeId
     * @return string
     */
    public function getTransiteoProductSku(ExtensibleDataInterface $product, int $storeId = null):string
    {
        $productIdentifier = $this->getProductIdentifier();
        if($productIdentifier === 'sku'){
            $sku = $product->getSku();
        }else{
            $sku = $product->getData($productIdentifier);
        }
        $sku = \mb_convert_encoding($sku, 'ASCII', 'UTF-8');
        $sku = str_replace(' ', '_', $sku);
        return ($storeId ?? $product->getStoreId()) . '_' . $sku;
    }

    /**
     * @return bool
     */
    public function getIsPriceIncludingTaxes(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::CONFIG_PATH_PRICE_INCLUDES_TAXES, ScopeInterface::SCOPE_STORE);
    }

    public function getTaxesCalculationMethod(): string
    {
        return (string) $this->scopeConfig->getValue(self::CONFIG_PATH_TAX_CALCULATION_METHOD, ScopeInterface::SCOPE_STORE);
    }

}
