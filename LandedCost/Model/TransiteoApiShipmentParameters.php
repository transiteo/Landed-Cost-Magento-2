<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\LandedCost\Model;

use Magento\Framework\Serialize\SerializerInterface;

class TransiteoApiShipmentParameters
{
    private $serializer;
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

    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    public function buildArray()
    {
        $array = [
            "lang" => $this->lang,
            "from_country" => $this->fromCountry,
//            "from_district" => $this->fromDistrict,
            "to_country" => $this->toCountry,
            "to_district" => $this->toDistrict,
            "shipment_type" => $this->shipmentType,
            'included_tax' => $this->isIncludedTaxes,
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

    public function buildArrayForCache(){
        $result = $this->buildArray();
        $array = [
            $result["to_country"],
            $result["to_district"],
            $result["shipment_type"],
            $result["global_ship_price"],
            $result['included_tax'],
            $result['incoterm'],
        ];
        if ($this->shipmentType ==='GLOBAL') {
            $array[] = $result["global_ship_price"];
            $array[] = $result["currency_global_ship_price"];
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
     * @param bool $isGlobal
     * @param float $globalShipPrice
     * @param string $currencyGlobalShipPrice
     *
     * @return  self
     */
    public function setShipmentType($isGlobal, $globalShipPrice = null, $currencyGlobalShipPrice = null)
    {
        if ($isGlobal) {
            $this->shipmentType = "GLOBAL";
            $this->globalShipPrice = $globalShipPrice;
            $this->currencyGlobalShipPrice = $currencyGlobalShipPrice;
        } else {
            $this->shipmentType = "ARTICLE";
            $this->globalShipPrice = null;
            $this->currencyGlobalShipPrice = null;
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
