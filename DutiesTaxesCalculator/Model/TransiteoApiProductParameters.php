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

    /**
     * @return array
     * @throws \Exception
     */
    public function buildArray()
    {
        $array = [];

        $array['identification']['type'] = "TEXT";
        $array['identification']['value'] = $this->productName;
        if (isset($this->weight) &&  $this->weight > 0) {
            $array['weight'] = $this->weight;
            $array['weight_unit'] = $this->weight_unit;
        } else {
            if (!isset($this->unit_ship_price)) {
                throw new \Exception('Transiteo Taxes : Unit ship price must be mentioned if weight is equal to zero.');
            }
            /** TODO not working with weight = 0; default weight set to 0.01kg*/
            $array['weight'] = 0.01;
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
}
