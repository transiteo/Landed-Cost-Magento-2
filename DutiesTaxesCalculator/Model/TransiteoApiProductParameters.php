<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\DutiesTaxesCalculator\Model;

class TransiteoApiProductParameters
{
    private $productName;
    private $weight;
    private $weight_unit;
    private $quantity;
    private $unit_price;
    private $currency_unit_price;
    private $unit_ship_price;
    private $sku;

    /**
     * @return array
     * @throws \Exception
     */
    public function buildArray()
    {
        $array = [];

        if(isset($this->sku)){
            $array['identification']['type'] = "SKU";
            $array['identification']['value'] = $this->sku;
        }else{
            $array['identification']['type'] = "TEXT";
            $array['identification']['value'] = $this->productName;
        }

        if (isset($this->weight) &&  $this->weight > 0) {
            $array['weight'] = $this->weight;
            $array['weight_unit'] = $this->weight_unit;
        } else {
            if (!isset($this->unit_ship_price)) {
                throw new \Exception('Transiteo Taxes : Unit ship price must be mentioned if weight is equal to zero.');
            }
            /** TODO not working with weight = 0; default weight set to 1kg*/
            $array['weight'] = 1;
            $array['weight_unit'] = "kg";
        }
        $array['quantity'] = $this->quantity;
        $array['unit_price'] = $this->unit_price;
        $array['currency_unit_price'] = $this->currency_unit_price;
        $array['unit_ship_price'] = $this->unit_ship_price;
        //$array['currency_unit_ship_price'] = $this->currency_unit_price;

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

    /**
     * @param mixed $sku
     */
    public function setSku($sku): void
    {
        $this->sku = $sku;
    }

    /**
     * @return mixed
     */
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return mixed
     */
    public function getWeightUnit()
    {
        return $this->weight_unit;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return mixed
     */
    public function getUnitPrice()
    {
        return $this->unit_price;
    }

    /**
     * @return mixed
     */
    public function getCurrencyUnitPrice()
    {
        return $this->currency_unit_price;
    }

    /**
     * @return mixed
     */
    public function getUnitShipPrice()
    {
        return $this->unit_ship_price;
    }

    /**
     * @return mixed
     */
    public function getSku()
    {
        return $this->sku;
    }
}
