<?php

declare(strict_types=1);

namespace Transiteo\Taxes\Controller\Test;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Transiteo\Base\Model\TransiteoApiShipmentParameters;
use Transiteo\Base\Model\TransiteoApiSingleProductParameters;
use Transiteo\Taxes\Model\TransiteoProducts;
use Transiteo\Taxes\Model\TransiteoSingleProduct;

class Request extends Action
{
    protected $singleProduct;
    protected $shipmentParams;
    protected $productParams;
    protected $transiteoProducts;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        TransiteoSingleProduct $singleProduct,
        TransiteoProducts $transiteoProducts,
        TransiteoApiSingleProductParameters $productParams,
        TransiteoApiShipmentParameters $shipmentParams
    ) {
        $this->transiteoProducts = $transiteoProducts;
        $this->singleProduct = $singleProduct;
        $this->shipmentParams = $shipmentParams;
        $this->productParams = $productParams;
        parent::__construct($context);
    }

    public function execute()
    {
        //$product->get($id);
        $this->productParams->setProductName("Chapeau de cowboy enfant");
        $this->productParams->setWeight(0);
        $this->productParams->setWeight_unit("kg");
        $this->productParams->setQuantity(10);
        $this->productParams->setUnit_price(10);
        $this->productParams->setCurrency_unit_price("EUR");
        $this->productParams->setUnit_ship_price(10);

        $this->shipmentParams->setLang("fr");
        $this->shipmentParams->setToCountry("FRA");
        $this->shipmentParams->setToDistrict("FR-GES");
        $this->shipmentParams->setFromCountry("CHN");
        $this->shipmentParams->setFromDistrict("CN-BJ");

        $this->shipmentParams->setShipmentType("GLOBAL", 100, "EUR");

        $this->shipmentParams->setSenderPro(true, 10000000, "EUR");
        $this->shipmentParams->setReceiverPro(false);
        //$this->shipmentParams->setReceiverPro(true,"0144Z");

        $this->shipmentParams->setTransportCarrier(null);
        $this->shipmentParams->setTransportType(null);


        $this->transiteoProducts->setProducts(["cowboy" => $this->productParams]);
        $this->transiteoProducts->setShipmentParams($this->shipmentParams);


        /** @var Json $jsonResult */
        $jsonResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $jsonResult->setData([
            "duty" => $this->transiteoProducts->getTotalDuty(),
            "vat" => $this->transiteoProducts->getTotalVat(),
            "special_taxes" => $this->transiteoProducts->getTotalSpecialTaxes(),
            "total" => $this->transiteoProducts->getTotalTaxes()
            ]);

        return $jsonResult;
    }
}
