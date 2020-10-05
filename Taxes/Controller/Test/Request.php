<?php

declare(strict_types=1);

namespace Transiteo\Taxes\Controller\Test;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Transiteo\Base\Model\TransiteoApiShipmentParameters;
use Transiteo\Base\Model\TransiteoApiSingleProductParameters;
use Transiteo\Taxes\Model\TransiteoSingleProduct;

class Request extends Action{

    protected $singleProduct;
    protected $shipmentParams;
    protected $productParams;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        TransiteoSingleProduct $singleProduct,
        TransiteoApiSingleProductParameters $productParams,
        TransiteoApiShipmentParameters $shipmentParams
    ) {
        $this->singleProduct = $singleProduct;
        $this->shipmentParams = $shipmentParams;
        $this->productParams = $productParams;
        parent::__construct($context);
    }

    public function execute(){

        //$product->get($id);
        $this->productParams->setProductName("audi rs8");
        $this->productParams->setWeight(657);
        $this->productParams->setWeight_unit("kg");
        $this->productParams->setQuantity(1);
        $this->productParams->setUnit_price(38);
        $this->productParams->setCurrency_unit_price("EUR");
        $this->productParams->setUnit_ship_price(57.6);


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
        $this->shipmentParams->setReceiverActivity("0144Z");

        $this->singleProduct->setParams($this->productParams);
        $this->singleProduct->setShipmentParams($this->shipmentParams);
        


            /** @var Json $jsonResult */
        $jsonResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $jsonResult->setData([
            "duty" => $this->singleProduct->getDuty(),
            "vat" => $this->singleProduct->getVat(),
            "special_taxes" => $this->singleProduct->getSpecialTaxes(),
            "total" => $this->singleProduct->getTotalTaxes()
            ]);
        
        return $jsonResult;

    }


}