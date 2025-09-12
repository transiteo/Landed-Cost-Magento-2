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

use Magento\Framework\Serialize\SerializerInterface;

class TransiteoApiShipmentParameters
{
    private $lang;
    private $fromCountry;
    private $fromDistrict;
    private $toCountry;
    private $toDistrict;
    private $shipmentType;
    private $globalShipPrice;
    private $currencyGlobalShipPrice;
    private $senderPro;
    private $senderProRevenue;
    private $senderProRevenueCurrency;
    private $transportType;
    private $transportCarrier;
    private $receiverPro;
    private $receiverActivity;
    /**
     * @var bool
     */
    protected $isIncludedTaxes;

    protected $taxesCalculationMethod;

    /**
     * @var string
     */
    protected $salesTerm;
    /**
     * @var string
     */
    protected $ecommerceType;
    /**
     * @var float
     */
    protected $extraFees;

    public function buildArray()
    {
        $array = [
            "lang" => $this->lang,
            "from_country" => $this->fromCountry,
//            "from_district" => $this->fromDistrict,
            "to_country" => $this->toCountry,
            "to_district" => $this->toDistrict,
            "shipment_type" => $this->shipmentType,
            'included_tax_from_country' => $this->isIncludedTaxes,
            'incoterm' => $this->taxesCalculationMethod,
            "sender" => [
                "pro" => $this->senderPro,
                "revenue_country_annual" => $this->senderProRevenue,
                "currency_revenue_country_annual" => $this->senderProRevenueCurrency
            ],
            "receiver" => [
                "pro" => $this->receiverPro
            ]

        ];

        if(isset($this->fromCountry)){
            $array["from_country"] = $this->fromCountry;

            if(isset($this->fromDistrict)){
                $array["from_district"] = $this->fromDistrict;
            }
        }

        if ($this->shipmentType ==='GLOBAL') {
            $array["global_ship_price"] = $this->globalShipPrice;
            $array["currency_global_ship_price"] = $this->currencyGlobalShipPrice;
        }

        if ($this->shipmentType ==='GROUP') {
            if (isset($array["from_country"])) {
                unset($array["from_country"]);
            }
            if (isset($array["from_district"])) {
                unset($array["from_district"]);
            }
            if (isset($array["to_country"])) {
                unset($array["to_country"]);
            }
            if (isset($array["to_district"])) {
                unset($array["to_district"]);
            }
            if (isset($array["sender"])) {
                unset($array["sender"]);
            }
            if (isset($array["global_ship_price"])) {
                unset($array["global_ship_price"]);
            }
            if (isset($array["currency_global_ship_price"])) {
                unset($array["currency_global_ship_price"]);
            }
        }

        if(isset($this->salesTerm)){
            $array["sales_term"] = $this->salesTerm;
        }
        if(isset($this->ecommerceType)){
            $array["ecommerce_type"] = $this->ecommerceType;
        }
        if(isset($this->extraFees)){
            $array["extra_fees"] = $this->extraFees;
        }

        if ($this->transportCarrier != null) {
            $array['transport'] = [
                "type" => $this->transportType,
                "id" => $this->transportCarrier
            ];
        }

        if ($this->receiverPro != false) {
            $array["receiver"]["activity_id"] = $this->receiverActivity;
        }

        return $array;
    }

    /**
     * Set Is Included Taxes :
     * @param bool $value
     * @return TransiteoApiShipmentParameters
     */
    public function setIsIncludedTaxes(bool $value):TransiteoApiShipmentParameters
    {
        $this->isIncludedTaxes = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIncludedTaxes(): bool
    {
        return $this->isIncludedTaxes;
    }

    /**
     * @return string
     */
    public function getTaxesCalculationMethod():?string
    {
        return $this->taxesCalculationMethod;
    }

    /**
     * @param mixed $taxesCalculationMethod
     */
    public function setTaxesCalculationMethod($taxesCalculationMethod):TransiteoApiShipmentParameters
    {
        $this->taxesCalculationMethod = $taxesCalculationMethod;
        return $this;
    }



    /**
     * Set the value of fromCountry
     *
     * @param $fromCountry
     * @return  self
     */
    public function setFromCountry($fromCountry)
    {
        $this->fromCountry = $fromCountry;

        return $this;
    }

    /**
     * Set the value of fromDistrict
     *
     * @param $fromDistrict
     * @return  self
     */
    public function setFromDistrict($fromDistrict)
    {
        $this->fromDistrict = $fromDistrict;

        return $this;
    }

    /**
     * Set the value of toCountry
     *
     * @param $toCountry
     * @return  self
     */
    public function setToCountry($toCountry)
    {
        $this->toCountry = $toCountry;

        return $this;
    }

    /**
     * Set the value of toDistrict
     *
     * @param $toDistrict
     * @return  self
     */
    public function setToDistrict($toDistrict)
    {
        $this->toDistrict = $toDistrict;

        return $this;
    }

    /**
     * Set the value of shipmentType
     *
     * @param string $type
     * @param float $globalShipPrice
     * @param string $currencyGlobalShipPrice
     *
     * @return  self
     */
    public function setShipmentType($type = "GLOBAL", $globalShipPrice = null, $currencyGlobalShipPrice = null)
    {
        if ($type === "GLOBAL") {
            $this->shipmentType = $type;
            $this->globalShipPrice = $globalShipPrice;
            $this->currencyGlobalShipPrice = $currencyGlobalShipPrice;
            return $this;
        }

        if ($type === "ARTICLE") {
            $this->shipmentType = $type;
            $this->globalShipPrice = $globalShipPrice;
            $this->currencyGlobalShipPrice = $currencyGlobalShipPrice;
            return $this;
        }

        /**
         * @TODO hardocoded
         */
        if ($type === "GROUP") {
            $this->shipmentType = $type;
            $this->globalShipPrice = null;
            $this->currencyGlobalShipPrice = null;
            $this->salesTerm = "btoc";
            $this->ecommerceType = "MARKETPLACE";
            $this->extraFees = 0.03;
            $this->isIncludedTaxes = true;
            return $this;
        }

        return $this;
    }

    /**
     *
     * Define if Sender is Pro and provide required parameters if pro.
     *
     * @param $isPro
     * @param double $senderProRevenue
     * @param string $senderProRevenueCurrency
     *
     * @return self
     */
    public function setSenderPro($isPro, $senderProRevenue = null, $senderProRevenueCurrency = null)
    {
        if ($isPro) {
            $this->senderPro = true;
            $this->senderProRevenue = $senderProRevenue;
            $this->senderProRevenueCurrency = $senderProRevenueCurrency;
        } else {
            $this->senderPro = false;
        }

        return $this;
    }

    /**
     * Set the value of transportType
     *
     * @param $transportType
     * @return  self
     */
    public function setTransportType($transportType)
    {
        $this->transportType = $transportType;

        return $this;
    }

    /**
     * Set the value of transportCarrier
     *
     * @param $transportCarrier
     * @return  self
     */
    public function setTransportCarrier($transportCarrier)
    {
        $this->transportCarrier = $transportCarrier;

        return $this;
    }

    /**
     * @param bool $isReceiverPro
     * @param string $receiverActivity
     * @return $this
     */
    public function setReceiverPro($isReceiverPro, $receiverActivity = null)
    {
        if ($isReceiverPro) {
            $this->receiverPro = true;
            $this->receiverActivity = $receiverActivity;
        } else {
            $this->receiverPro = false;
        }
        return $this;
    }

    /**
     * Set the value of lang
     *
     * @return  self
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @return mixed
     */
    public function getFromCountry()
    {
        return $this->fromCountry;
    }

    /**
     * @return mixed
     */
    public function getFromDistrict()
    {
        return $this->fromDistrict;
    }

    /**
     * @return mixed
     */
    public function getToCountry()
    {
        return $this->toCountry;
    }

    /**
     * @return mixed
     */
    public function getToDistrict()
    {
        return $this->toDistrict;
    }

    /**
     * @return mixed
     */
    public function getShipmentType()
    {
        return $this->shipmentType;
    }

    /**
     * @return mixed
     */
    public function getGlobalShipPrice()
    {
        return $this->globalShipPrice;
    }

    /**
     * @return mixed
     */
    public function getCurrencyGlobalShipPrice()
    {
        return $this->currencyGlobalShipPrice;
    }

    /**
     * @return mixed
     */
    public function getSenderPro()
    {
        return $this->senderPro;
    }

    /**
     * @return mixed
     */
    public function getSenderProRevenue()
    {
        return $this->senderProRevenue;
    }

    /**
     * @return mixed
     */
    public function getSenderProRevenueCurrency()
    {
        return $this->senderProRevenueCurrency;
    }

    /**
     * @return mixed
     */
    public function getTransportType()
    {
        return $this->transportType;
    }

    /**
     * @return mixed
     */
    public function getTransportCarrier()
    {
        return $this->transportCarrier;
    }

    /**
     * @return mixed
     */
    public function getReceiverPro()
    {
        return $this->receiverPro;
    }

    /**
     * @return mixed
     */
    public function getReceiverActivity()
    {
        return $this->receiverActivity;
    }

}
