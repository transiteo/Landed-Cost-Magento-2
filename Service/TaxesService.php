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

namespace Transiteo\LandedCost\Service;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\FlagManager;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;
use Transiteo\LandedCost\Controller\Cookie;
use Transiteo\LandedCost\Logger\Logger;
use Transiteo\LandedCost\Model\Config;
use Transiteo\LandedCost\Model\TransiteoApiProductParameters;
use Transiteo\LandedCost\Model\TransiteoApiProductParametersFactory;
use Transiteo\LandedCost\Model\TransiteoApiShipmentParameters;
use Transiteo\LandedCost\Model\TransiteoApiShipmentParametersFactory;
use Transiteo\LandedCost\Model\TransiteoProducts;
use Transiteo\LandedCost\Model\TransiteoProductsFactory;

class TaxesService
{
    public const SHIPPING_AMOUNT = 'shipping_amount';
    public const TAXES_CALCULATION_METHOD = 'taxes_calculation_method';
    public const INCLUDED_TAX = 'included_tax';
    public const RECEIVER_PRO = 'receiver_pro';
    public const RECEIVER_ACTIVITY = 'receiver_activity';
    public const TO_COUNTRY = 'to_country';
    public const TO_DISTRICT = 'to_district';
    public const DISALLOW_GET_COUNTRY_FROM_COOKIE = 'disallow_get_country_from_cookie';

    public const RETURN_KEY_DUTY = 'duty';
    public const RETURN_KEY_VAT = 'vat';
    public const RETURN_KEY_SPECIAL_TAXES = 'special_taxes';
    public const RETURN_KEY_TOTAL_TAXES = 'total_taxes';
    public const COOKIE_NAME = Config::COOKIE_NAME;

    /**
     * @var TransiteoProductsFactory
     */
    protected $transiteoProductsFactory;

    /**
     * @var TransiteoApiProductParametersFactory
     */
    protected $productParamsFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var FlagManager
     */
    protected $_flagManager;
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var TransiteoApiShipmentParametersFactory
     */
    protected $shipmentParamsFactory;
    /**
     * @var Cookie
     */
    protected $cookie;

    /**
     * TaxesService constructor.
     * @param TransiteoProductsFactory $transiteoProductsFactory
     * @param StoreManagerInterface $storeManager
     * @param TransiteoApiProductParametersFactory $productParamsFactory
     * @param TransiteoApiShipmentParametersFactory $shipmentParamsFactory
     * @param FlagManager $flagManager
     * @param Logger $logger
     * @param Config $config
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Cookie $cookie
     */
    public function __construct(
        TransiteoProductsFactory $transiteoProductsFactory,
        StoreManagerInterface $storeManager,
        TransiteoApiProductParametersFactory $productParamsFactory,
        TransiteoApiShipmentParametersFactory $shipmentParamsFactory,
        FlagManager $flagManager,
        Logger $logger,
        Config $config,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Cookie $cookie
    ) {
        $this->logger = $logger;
        $this->transiteoProductsFactory = $transiteoProductsFactory;
        $this->shipmentParamsFactory = $shipmentParamsFactory;
        $this->productParamsFactory = $productParamsFactory;
        $this->storeManager = $storeManager;
        $this->_flagManager = $flagManager;
        $this->config = $config;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->cookie = $cookie;
    }


    /**
     * @param CartItemInterface[] $products array of quote items
     * @param array $params
     * @param bool $save
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getDutiesByQuoteItems(array $products, $params = [], bool $save = true): array
    {
        //SHIPMENT
        $shipmentParams = $this->shipmentParamsFactory->create();

        $this->fillShipmentParams($shipmentParams, count($products), $params);

        ///PRODUCTS
        $productsParams = [];

        foreach ($products as $quoteItem) {
            $qty = $quoteItem->getQty();
            $product = $quoteItem->getProduct();
            $price = (float)$quoteItem->getPrice() - ($quoteItem->getDeltaDiscount() ?? 0.0);
            /**
             * @var TransiteoApiProductParameters $productParams ;
             * @var ProductInterface $product ;
             */
            $productParams = $this->productParamsFactory->create();
            $this->fillProductParams($productParams, $product, $qty, 0, $price);
            $productsParams[$product->getId()] = $productParams;
        }

        $transiteoProducts = $this->transiteoProductsFactory->create();

        $transiteoProducts->setProducts($productsParams);
        $transiteoProducts->setShipmentParams($shipmentParams);


        if ($save) {
            $this->saveDutiesOnQuoteItems($products, $transiteoProducts);
        }

        return $this->formatDutiesResponse($transiteoProducts);
    }

    /**
     * @param TransiteoApiShipmentParameters $shipmentParams
     * @param float $productsQty
     * @param array $params
     * @return TransiteoApiShipmentParameters
     * @throws NoSuchEntityException
     */
    protected function fillShipmentParams(
        TransiteoApiShipmentParameters $shipmentParams,
        float $productsQty = 1,
        array $params = []
    ): TransiteoApiShipmentParameters {
        //get included Taxes
        if (array_key_exists(self::INCLUDED_TAX, $params)) {
            $shipmentParams->setIsIncludedTaxes($params[self::INCLUDED_TAX]);
        } else {
            $shipmentParams->setIsIncludedTaxes($this->config->getIsPriceIncludingTaxes());
        }

        //get incoterm
        if (array_key_exists(self::TAXES_CALCULATION_METHOD, $params)) {
            $shipmentParams->setTaxesCalculationMethod($params[self::TAXES_CALCULATION_METHOD]);
        } else {
            $shipmentParams->setTaxesCalculationMethod($this->config->getTaxesCalculationMethod());
        }


        //get shipping amount
        if (array_key_exists(self::SHIPPING_AMOUNT, $params)) {
            $shippingAmount = $params[self::SHIPPING_AMOUNT];
        } else {
            $shippingAmount = 0;
        }

        $shipmentParams->setShipmentType(true, round($shippingAmount, 2), $this->getCurrentStoreCurrency());

        $shipmentParams->setLang($this->getTransiteoLang());

//        $shipmentParams->setFromCountry($this->config->getIso3Country($this->config->getWebsiteCountry())); // country from website ISO3
        $shipmentParams->setFromCountry($this->config->getIso3Country("CH")); // country from website ISO3

        /** TODO add from district in config */
        $shipmentParams->setFromDistrict($this->config->getWebsiteDistrict()); // district from DistrictRepository

        //GET to country and to district from params or cookie
        if ((!array_key_exists(self::TO_COUNTRY, $params))) {
            if ((array_key_exists(self::DISALLOW_GET_COUNTRY_FROM_COOKIE, $params)
                && $params[self::DISALLOW_GET_COUNTRY_FROM_COOKIE])) {
                throw new Exception("Transiteo_LandedCost getting country from cookie is disallowed.");
            }
            $cookie = $this->cookie->get(Config::COOKIE_NAME, null);
            if ($cookie === null) {
                throw new Exception("Transiteo_LandedCost country cookie does not exists.");
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
                //Set to district = "NOTSET" if not required
                $toDistrict = $this->getRequiredDefaultDistrict($toCountry);
            }
        }

        //IF country is ISO2 get ISO3 code
        if (strlen($toCountry) === 2) {
            $toCountry = $this->config->getIso3Country($toCountry);
        }
        $shipmentParams->setToCountry($toCountry); // country from customer attribute or cookie value
        $shipmentParams->setToDistrict($toDistrict); // district from customer attribute or cookie value

        /**
         * TODO add Sender pro in config
         */
//        $shipmentParams->setSenderPro(true, 1, "EUR"); // true always, const
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

        return $shipmentParams;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    private function getCurrentStoreCurrency()
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Get transiteo lang Code
     *
     * @return string
     */
    public function getTransiteoLang(): string
    {
        $locale = substr($this->config->getLocale(), 0, 3);

        if ($locale === "fr_") {
            return "fr";
        }
        if ($locale === "es_") {
            return "es";
        }
        return "en";
    }

    /**
     * @param string $toCountry
     * @return string
     */
    protected function getRequiredDefaultDistrict(string $toCountry): string
    {
        $toDistrict = "NOTSET";
        //set default district for usa, and Brazil and Canada.
        if ($toCountry === "US") {
            $toDistrict = "US-CA-90034";
        }
        if ($toCountry === "CA") {
            $toDistrict = "CA-AB";
        }
        if ($toCountry === "BR") {
            $toDistrict = "BR-AC";
        }
        return $toDistrict;
    }

    /**
     * @param TransiteoApiProductParameters $productParams
     * @param ProductInterface $product
     * @param float $qty
     * @param float|int $globalShipPrice
     * @param float|null $overridePrice
     * @return TransiteoApiProductParameters
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function fillProductParams(
        TransiteoApiProductParameters $productParams,
        ProductInterface $product,
        float $qty = 1,
        float $globalShipPrice = 0,
        ?float $overridePrice = null
    ): TransiteoApiProductParameters {
        $productParams->setSku($this->config->getTransiteoProductSku($product));
        $productParams->setProductName($product->getName());
        $productParams->setWeight(round($product->getWeight(), 2));
        $productParams->setWeight(0);
        $productParams->setWeight_unit($this->config->getWeightUnit());
        $productParams->setQuantity($qty);
        $productParams->setUnit_price(round((($overridePrice ?? $product->getFinalPrice()) * $this->getCurrentCurrencyRate()) / 1.2,
            2));
        $productParams->setCurrency_unit_price($this->getCurrentStoreCurrency());
        if ($globalShipPrice === 0) {
            $productParams->setUnit_ship_price(0); // 0 default
        } else {
            $productParams->setUnit_ship_price(round($globalShipPrice / $qty, 2)); // prix du shipping
        }
        return $productParams;
    }

    /**
     * @return float
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCurrentCurrencyRate(): float
    {
        return $this->storeManager->getStore()->getCurrentCurrencyRate();
    }

    /**
     * @param CartItemInterface[] $quoteItems
     * @param TransiteoProducts $transiteoProducts
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function saveDutiesOnQuoteItems(array $quoteItems, TransiteoProducts $transiteoProducts): void
    {
        foreach ($quoteItems as $quoteItem) {
            $product = $quoteItem->getProduct();
            /**
             * @var CartItemInterface $product
             */
            $id = (int)$product->getId();

            $currencyRate = $this->getCurrentCurrencyRate();
            $duty = $transiteoProducts->getDuty($id);
            $specialTaxes = $transiteoProducts->getSpecialTaxes($id);
            $totalTaxes = $transiteoProducts->getTotalTaxes($id);
            $vatAmount = $transiteoProducts->getVat($id);

            //Set Transiteo Taxes
            $quoteItem->setData('transiteo_vat', $vatAmount);
            $quoteItem->setData('transiteo_duty', $duty);
            $quoteItem->setData('transiteo_special_taxes', $specialTaxes);
            $quoteItem->setData('transiteo_total_taxes', $totalTaxes);

            if ($currencyRate === 1.0) {
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

            //if taxes have been retrieved

            if (isset($totalTaxes)) {
                //Set Tax Amount if incoterm is ddp
                if ($this->isDDPActivated() && !$this->config->getIsPriceIncludingTaxes()) {
                    $quoteItem->setTaxAmount($totalTaxes ?? 0);
                    $quoteItem->setBaseTaxAmount($totalTaxes / $currencyRate);
                }
                //tax percent is included in every cases.
                $quoteItem->setTaxPercent($transiteoProducts->getProductTaxPercent($id));
            }

        }
    }

    /**
     *  Return if ddp is activate.
     *
     * @return bool|mixed
     */
    public function isDDPActivated()
    {
        return $this->config->isDDPActivated();
    }

    /**
     * @param TransiteoProducts $products
     * @return array
     */
    protected function formatDutiesResponse(TransiteoProducts $products): array
    {
        return [
            self::RETURN_KEY_DUTY => $products->getTotalDuty(),
            self::RETURN_KEY_VAT => $products->getTotalVat(),
            self::RETURN_KEY_SPECIAL_TAXES => $products->getTotalSpecialTaxes(),
            self::RETURN_KEY_TOTAL_TAXES => $products->getTotalTaxes()
        ];
    }

    /**
     * @param string $sku
     * @param float $qty
     * @param int|null $storeId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getDutiesByProductSku(string $sku, float $qty = 1, ?int $storeId = null): array
    {

        $this->searchCriteriaBuilder
            ->addFilter($this->config->getProductIdentifier(), $sku, "eq");

        if (isset($storeId)) {
            $this->searchCriteriaBuilder->addFilter('store_id', $storeId, "eq");
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();


        $products = $this->productRepository->getList($searchCriteria)->getItems();
        if (!empty($products)) {
            $product = reset($products);
            $shipmentParams = $this->shipmentParamsFactory->create();
            $productParams = $this->productParamsFactory->create();
            $this->fillShipmentParams($shipmentParams, $qty);
            $this->fillProductParams($productParams, $product, $qty);
            $transiteoProducts = $this->transiteoProductsFactory->create();
            $transiteoProducts->setProducts([$product->getId() => $productParams]);
            $transiteoProducts->setShipmentParams($shipmentParams);

            $response = $this->formatDutiesResponse($transiteoProducts);
            $this->logger->debug(\json_encode($response));
            return $response;
        }
        return [];
    }

    /**
     * @return \Magento\Directory\Model\Country|null
     */
    public function getToCountryFromCookie(): ?\Magento\Directory\Model\Country
    {
        $cookie = $this->cookie->get(Config::COOKIE_NAME, null);
        if (!isset($cookie)) {
            return null;
        }
        $cookie = explode('_', $cookie);
        $toCountry = $cookie[0];
        $country = $this->config->getCountryFactory()->create();
        return $country->loadByCode($toCountry);
    }

    /**
     * @param bool|null|string $country
     * @param bool|null|string $district
     * @param bool|null|string $currency
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function updateCookieValue($country = false, $district = false, $currency = false): string
    {
        $cookie = $this->cookie->get(self::COOKIE_NAME, null);
        if (isset($cookie)) {
            $cookie = explode('_', $cookie);
            if (!$country && isset($country)) {
                $country = $cookie[0];
            }
            if ($country !== $cookie[0]) {
                if (!$district && isset($district)) {
                    $district = "";
                }
            } elseif (!$district && isset($district)) {
                $district = $cookie[1];
            }

            if (!$currency && isset($currency)) {
                $currency = $cookie[2];
            }

        }
        $value = implode("_", [
            $country ?: $this->config->getWebsiteCountry(),
            $district ?: "",
            $currency ?: $this->getCurrentStoreCurrency()
        ]);

        $this->cookie->set(
            Config::COOKIE_NAME, $value
        );
        return $value;
    }

    /**
     *  Return if ddp is activate.
     *
     * @return bool|mixed
     */
    public function getIncoterm()
    {
        return $this->config->getIncoterm();
    }

    /**
     * Return true if is activated on checkout
     *
     * @return bool
     */
    public function isActivatedOnCheckout(): bool
    {
        return $this->config->isActivatedOnCheckout();
    }

    /**
     * Return true if is activated on cart view
     *
     * @return bool
     */
    public function isActivatedOnCartView(): bool
    {
        return $this->config->isActivatedOnCartView();
    }

    /**
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }


}
