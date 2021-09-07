<?php

/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */


declare(strict_types=1);

namespace Transiteo\DutiesTaxesCalculator\Model;

use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const COOKIE_NAME = 'transiteo-popup-info';
    public const CONFIG_PATH_PDP_LOADER_ENABLED = 'transiteo_settings/pdp_settings/enable_loader';
    public const CONFIG_PATH_PDP_PRODUCT_FORM_SELECTOR = 'transiteo_settings/pdp_settings/product_form_selector';
    public const CONFIG_PATH_PDP_QTY_FIELD_SELECTOR = 'transiteo_settings/pdp_settings/qty_field_selector';
    public const CONFIG_PATH_PDP_TOTAL_TAXES_CONTAINER_SELECTOR = 'transiteo_settings/pdp_settings/total_taxes_container_selector';
    public const CONFIG_PATH_PDP_VAT_CONTAINER_SELECTOR = 'transiteo_settings/pdp_settings/vat_container_selector';
    public const CONFIG_PATH_PDP_DUTY_CONTAINER_SELECTOR = 'transiteo_settings/pdp_settings/duty_container_selector';
    public const CONFIG_PATH_PDP_SPECIAL_TAXES_CONTAINER_SELECTOR = 'transiteo_settings/pdp_settings/special_taxes_container_selector';
    public const CONFIG_PATH_PDP_COUNTRY_SELECTOR = 'transiteo_settings/pdp_settings/country_selector';
    public const CONFIG_PATH_PDP_SUPER_ATTRIBUTE_SELECTOR = 'transiteo_settings/pdp_settings/super_attribute_selector';
    public const CONFIG_PATH_PDP_EVENT_ACTION = 'transiteo_settings/pdp_settings/event_action';
    public const CONFIG_PATH_PDP_DELAY = 'transiteo_settings/pdp_settings/delay';

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
     * @param ScopeConfigInterface $scopeConfig
     * @param RegionFactory $regionFactory
     * @param CountryFactory $countryFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RegionFactory $regionFactory,
        CountryFactory $countryFactory
    ) {
        $this->regionFactory = $regionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->countryFactory = $countryFactory;
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
            ScopeInterface::SCOPE_WEBSITE
        );
        $region = $this->regionFactory->create()->load($regionId)->getCode();
        $zip = $this->scopeConfig->getValue(
            'shipping/origin/postcode',
            ScopeInterface::SCOPE_WEBSITE
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
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return mixed
     */
    public function getWeightUnit(): string
    {
        $unit = $this->scopeConfig->getValue(
            'general/locale/weight_unit',
            ScopeInterface::SCOPE_STORE
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
            'transiteo_settings/duties/enabled_on',
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
            'transiteo_settings/duties/enabled_on',
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
            'transiteo_activation/general/product_identifier',
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
            ScopeInterface::SCOPE_WEBSITE
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
                'transiteo_settings/duties/incoterm',
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
            'transiteo_settings/duties/incoterm',
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
            'transiteo_settings/duties/enabled_on',
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
        return $this->scopeConfig->isSetFlag(self::CONFIG_PATH_PDP_LOADER_ENABLED, ScopeInterface::SCOPE_WEBSITE);
    }


    /**
     * @return string
     */
    public function getPDPProductFormSelector(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_PATH_PDP_PRODUCT_FORM_SELECTOR,
            ScopeInterface::SCOPE_WEBSITE
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
            ScopeInterface::SCOPE_WEBSITE
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
            ScopeInterface::SCOPE_WEBSITE
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
            ScopeInterface::SCOPE_WEBSITE
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
            ScopeInterface::SCOPE_WEBSITE
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
            ScopeInterface::SCOPE_WEBSITE
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
            ScopeInterface::SCOPE_WEBSITE
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
            ScopeInterface::SCOPE_WEBSITE
        );
    }


    /**
     * Retrieve the event action
     *
     * @return string
     */
    public function getPDPEventAction(): string
    {
        return (string) $this->scopeConfig->getValue(self::CONFIG_PATH_PDP_EVENT_ACTION, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Retrieve the delay sync
     *
     * @return int
     */
    public function getPDPDelay(): int
    {
        return (int) $this->scopeConfig->getValue(self::CONFIG_PATH_PDP_DELAY, ScopeInterface::SCOPE_WEBSITE);
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

}
