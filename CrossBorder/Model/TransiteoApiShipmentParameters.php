<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\CrossBorder\Model;

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
            "from_district" => $this->fromDistrict,
            "to_country" => $this->toCountry,
            "to_district" => $this->toDistrict,
            "shipment_type" => $this->shipmentType,
            "sender" => [
                "pro" => $this->senderPro,
                "revenue_country_annual" => $this->senderProRevenue,
                "currency_revenue_country_annual" => $this->senderProRevenueCurrency
            ],
            "receiver" => [
                "pro" => $this->receiverPro
            ]

        ];

        if ($this->shipmentType==='GLOBAL') {
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
}
