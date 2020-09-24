<?php
/**
 * @author Joris HART <jhart@efaktory.fr>
 * @copyright Copyright (c) 2020 eFaktory (https://www.efaktory.fr)
 * @link https://www.efaktory.fr
 */
declare(strict_types=1);

namespace Transiteo\Taxes\Service;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
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

    public function __construct(
        TransiteoSingleProduct $singleProduct,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        TransiteoApiSingleProductParameters $productParams,
        TransiteoApiShipmentParameters $shipmentParams,
        ProductRepositoryInterface $productRepository
    ) {
        $this->singleProduct     = $singleProduct;
        $this->shipmentParams    = $shipmentParams;
        $this->productParams     = $productParams;
        $this->productRepository = $productRepository;
        $this->scopeConfig       = $scopeConfig;
        $this->storeManager      = $storeManager;
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
        $this->productParams->setWeight($product->getWeight());
        $this->productParams->setWeight_unit($this->getWeightUnit());
        $this->productParams->setQuantity($quantity);
        $this->productParams->setUnit_price($product->getPrice());
        $this->productParams->setCurrency_unit_price($this->getCurrentStoreCurrency());

        /**
         * @todo Which value has to be filed in here ??
         */
        $this->productParams->setUnit_ship_price(567.6);


        $this->shipmentParams->setLang("fr");
        $this->shipmentParams->setFromCountry("FRA");
        $this->shipmentParams->setFromDistrict("FR-GES");
        $this->shipmentParams->setToCountry("USA");
        $this->shipmentParams->setToDistrict("US-MO-65055");
        $this->shipmentParams->setShipmentType("ARTICLE");

        $this->shipmentParams->setSenderPro(true);
        $this->shipmentParams->setSenderProRevenue(3450000);
        $this->shipmentParams->setSenderProRevenueCurrency("EUR");

        $this->shipmentParams->setTransportCarrier(null);
        $this->shipmentParams->setTransportType(null);

        $this->shipmentParams->setReceiverPro(true);

        /**
         * @todo Which value has to be filed in here ??
         */
        $this->shipmentParams->setReceiverActivity("0144Z");

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

}
