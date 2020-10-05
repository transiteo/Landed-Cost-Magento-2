<?php

namespace Transiteo\Taxes\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Transiteo\Base\Model\TransiteoApiService;
use Transiteo\Base\Model\TransiteoApiShipmentParameters;
use Transiteo\Base\Model\TransiteoApiSingleProductParameters;

class TransiteoSingleProduct
{

    private $apiService;
    private $apiResponseContent;
    private $serializer;
    private $params;
    private $shipmentParams;

    public function __construct(
        TransiteoApiService $apiService,
        TransiteoApiSingleProductParameters $params,
        TransiteoApiShipmentParameters $shipmentParams,
        SerializerInterface $serializer
    ) {
        $this->apiService = $apiService;
        $this->params = $params;
        $this->shipmentParams = $shipmentParams;
        $this->serializer = $serializer;
    }

    public function getDuties(){

        $finalParams['products'][] = $this->params->buildArray();
        $finalParams = array_merge($finalParams, $this->shipmentParams->buildArray());

        $this->apiResponseContent = json_decode(($this->apiService->getDuties($finalParams)));

        if(isset($this->apiResponseContent->httpCode) && $this->apiResponseContent->httpCode != 200)
            return $this->apiResponseContent;
        else
            return true;
    }

    public function getDuty(){

        if($this->apiResponseContent == null){
            $response = $this->getDuties();
            if($response !== true){
                return null;
 
            }
        }

        $totalTax = 0;
        if(isset($this->apiResponseContent->products)){
            $totalTax += ($this->apiResponseContent->products[0]->duty->product_taxes_amount ?? 0 );
            $totalTax += ($this->apiResponseContent->products[0]->duty->vat_taxes_amount ?? 0 );
            $totalTax += ($this->apiResponseContent->products[0]->duty->shipping_taxes_amount ?? 0 );
        }
        else
            return null;
        
        
        return $totalTax;

    } 

    public function getVat(){
        if($this->apiResponseContent == null){
           $response = $this->getDuties();
           if($response !== true){
                return null;

           }
        }

        $totalTax = 0;

        if(isset($this->apiResponseContent->products)){
            foreach($this->apiResponseContent->products[0]->vat as $vat){
                if(isset($vat->product_taxes_amount))
                    $totalTax += $vat->product_taxes_amount + $vat->shipping_taxes_amount;
            }
        }
        else
            return null;
        

        return $totalTax;
    }

    public function getSpecialTaxes(){
        if($this->apiResponseContent == null){
            $response = $this->getDuties();
            if($response !== true){
                return null;
 
            }
        }

        $totalTax = 0;
        if(isset($this->apiResponseContent->products)){
            if(isset($this->apiResponseContent->products[0]->special_taxes->product_taxes_amount))
                $totalTax += ($this->apiResponseContent->products[0]->special_taxes->product_taxes_amount ?? 0 );
        }
        else
            return null;

        return $totalTax;

    }

    public function getTotalTaxes(){

        if($this->apiResponseContent == null){
            $response = $this->getDuties();
            if($response !== true){
                return null;
 
            }
        }

        $total = 0;

        if(isset($this->apiResponseContent->global)){
            $total = $this->apiResponseContent->global->amount;
        }
        else
            return null;

        return $total;
    }


    /**
     * Set the value of params
     *
     * @return  self
     */ 
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Set the value of shipmentParams
     *
     * @return  self
     */ 
    public function setShipmentParams($shipmentParams)
    {
        $this->shipmentParams = $shipmentParams;

        return $this;
    }

    /**
     * Get the value of apiService
     */ 
    public function getApiService()
    {
        return $this->apiService;
    }
}