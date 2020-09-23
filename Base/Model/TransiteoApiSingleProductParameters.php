<?php

namespace Transiteo\Base\Model;

use Magento\Framework\Serialize\SerializerInterface;

class TransiteoApiSingleProductParameters
{

    private $serializer;
    private $productName;
    private $weight;
    private $weight_unit;
    private $quantity;
    private $unit_price;
    private $currency_unit_price;
    private $unit_ship_price;


    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    public function setParameters(){
         
    }

    public function buildArray(){
        $array = array();

        $array['identification']['type'] = "TEXT";
        $array['identification']['value'] = $this->productName;
        $array['weight'] = $this->weight;
        $array['weight_unit'] = $this->weight_unit;
        $array['quantity'] = $this->quantity;
        $array['unit_price'] = $this->unit_price;
        $array['currency_unit_price'] = $this->currency_unit_price;
        $array['unit_ship_price'] = $this->unit_ship_price;

        return $array;
    }


    /**
     * Set the value of productName
     *
     * @return  self
     */ 
    public function setProductName($productName)
    {
        $this->productName = $productName;

        return $this;
    }

    /**
     * Set the value of weight_unit
     *
     * @return  self
     */ 
    public function setWeight_unit($weight_unit)
    {
        $this->weight_unit = $weight_unit;

        return $this;
    }

    /**
     * Set the value of weight
     *
     * @return  self
     */ 
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Set the value of quantity
     *
     * @return  self
     */ 
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Set the value of unit_price
     *
     * @return  self
     */ 
    public function setUnit_price($unit_price)
    {
        $this->unit_price = $unit_price;

        return $this;
    }

    /**
     * Set the value of currency_unit_price
     *
     * @return  self
     */ 
    public function setCurrency_unit_price($currency_unit_price)
    {
        $this->currency_unit_price = $currency_unit_price;

        return $this;
    }

    /**
     * Set the value of unit_ship_price
     *
     * @return  self
     */ 
    public function setUnit_ship_price($unit_ship_price)
    {
        $this->unit_ship_price = $unit_ship_price;

        return $this;
    }
}