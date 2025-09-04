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

namespace Transiteo\LandedCost\Model;

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
    
    // Nouveaux champs ajoutés
    private $group_shipping_price;
    private $from_country;
    private $to_country;
    private $included_tax;
    private $incoterm;
    private $sender_pro;
    private $seller_id;

    /**
     * @return array
     * @throws \Exception
     */
    public function buildArray()
    {
        $array = [];

        // Identification
        /*if(isset($this->sku)){
            $array['identification']['type'] = "SKU";
            $array['identification']['value'] = $this->sku;
        }else{
            $array['identification']['type'] = "TEXT";
            $array['identification']['value'] = $this->productName;
        }*/
        $array['identification']['type'] = "TEXT";
        $array['identification']['value'] = $this->productName;

        // Weight
        if (isset($this->weight) &&  $this->weight > 0) {
            $array['weight'] = $this->weight;
            $array['weight_unit'] = $this->weight_unit;
        } else {
            if (!isset($this->unit_ship_price) && !isset($this->group_shipping_price)) {
                throw new \Exception('Transiteo Taxes : Unit ship price or group shipping price must be mentioned if weight is equal to zero.');
            }
            /** TODO not working with weight = 0; default weight set to 1kg*/
            $array['weight'] = 1;
            $array['weight_unit'] = "kg";
        }
        
        $array['quantity'] = $this->quantity;
        $array['unit_price'] = $this->unit_price;
        
        // Utilisation de group_shipping_price au lieu de unit_ship_price
        if(isset($this->group_shipping_price) && $this->group_shipping_price > 0){
            $array['group_shipping_price'] = $this->group_shipping_price;
        } elseif(isset($this->unit_ship_price) && $this->unit_ship_price > 0){
            $array['unit_ship_price'] = $this->unit_ship_price;
        }
        
        $array['currency_unit_price'] = $this->currency_unit_price;
        
        // Nouveaux champs requis
        if(isset($this->from_country)){
            $array['from_country'] = $this->from_country;
        }
        
        if(isset($this->to_country)){
            $array['to_country'] = $this->to_country;
        }
        
        if(isset($this->included_tax)){
            $array['included_tax'] = $this->included_tax;
        }
        
        if(isset($this->incoterm)){
            $array['incoterm'] = $this->incoterm;
        }
        
        // Sender information
        if(isset($this->sender_pro) || isset($this->seller_id)){
            $array['sender'] = [];
            if(isset($this->sender_pro)){
                $array['sender']['pro'] = $this->sender_pro;
            }
            if(isset($this->seller_id)){
                $array['sender']['seller_id'] = $this->seller_id;
            }
        }

        return $array;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function builArrayForCache(){
        $result = $this->buildArray();
        $array = [
            $result['quantity'],
            $result['unit_price'],
            $result['currency_unit_price'],
        ];
        if(array_key_exists('unit_ship_price', $result)){
            $array[] = $result['unit_ship_price'];
        }
        if(array_key_exists('group_shipping_price', $result)){
            $array[] = $result['group_shipping_price'];
        }
        if(array_key_exists('from_country', $result)){
            $array[] = $result['from_country'];
        }
        if(array_key_exists('to_country', $result)){
            $array[] = $result['to_country'];
        }
        if(array_key_exists('included_tax', $result)){
            $array[] = $result['included_tax'];
        }
        if(array_key_exists('incoterm', $result)){
            $array[] = $result['incoterm'];
        }
        if(array_key_exists('sender', $result)){
            $array[] = $result['sender'];
        }
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

    // Nouveaux setters
    /**
     * Set the value of group_shipping_price
     *
     * @return  self
     */
    public function setGroup_shipping_price($group_shipping_price)
    {
        $this->group_shipping_price = $group_shipping_price;
        return $this;
    }

    /**
     * Set the value of from_country
     *
     * @return  self
     */
    public function setFrom_country($from_country)
    {
        $this->from_country = $from_country;
        return $this;
    }

    /**
     * Set the value of to_country
     *
     * @return  self
     */
    public function setTo_country($to_country)
    {
        $this->to_country = $to_country;
        return $this;
    }

    /**
     * Set the value of included_tax
     *
     * @return  self
     */
    public function setIncluded_tax($included_tax)
    {
        $this->included_tax = $included_tax;
        return $this;
    }

    /**
     * Set the value of incoterm
     *
     * @return  self
     */
    public function setIncoterm($incoterm)
    {
        $this->incoterm = $incoterm;
        return $this;
    }

    /**
     * Set the value of sender_pro
     *
     * @return  self
     */
    public function setSender_pro($sender_pro)
    {
        $this->sender_pro = $sender_pro;
        return $this;
    }

    /**
     * Set the value of seller_id
     *
     * @return  self
     */
    public function setSeller_id($seller_id)
    {
        $this->seller_id = $seller_id;
        return $this;
    }

    // Getters existants
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

    // Nouveaux getters
    /**
     * @return mixed
     */
    public function getGroup_shipping_price()
    {
        return $this->group_shipping_price;
    }

    /**
     * @return mixed
     */
    public function getFrom_country()
    {
        return $this->from_country;
    }

    /**
     * @return mixed
     */
    public function getTo_country()
    {
        return $this->to_country;
    }

    /**
     * @return mixed
     */
    public function getIncluded_tax()
    {
        return $this->included_tax;
    }

    /**
     * @return mixed
     */
    public function getIncoterm()
    {
        return $this->incoterm;
    }

    /**
     * @return mixed
     */
    public function getSender_pro()
    {
        return $this->sender_pro;
    }

    /**
     * @return mixed
     */
    public function getSeller_id()
    {
        return $this->seller_id;
    }
}
