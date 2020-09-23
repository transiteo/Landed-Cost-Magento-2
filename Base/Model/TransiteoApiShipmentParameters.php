<?php

namespace Transiteo\Base\Model;

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



    public function buildArray(){
        $array = array(

            "lang" => $this->lang,
            "from_country" => $this->fromCountry,
            "from_district" => $this->fromDistrict,
            "to_country" => $this->toCountry,
            "to_district" => $this->toDistrict,
            "shipment_type" => $this->shipmentType,
            "sender" => array(
                "pro" => $this->senderPro,
                "revenue_country_annual" => $this->senderProRevenue,
                "currency_revenue_country_annual" => $this->senderProRevenueCurrency
            ),
            "receiver" => array(
                "pro" => $this->receiverPro
            )

        );

        
        if($this->transportCarrier != null){
            $array['transport'] = array(
                "type" => $this->transportType,
                "id" => $this->transportCarrier
            );
        }

        if($this->receiverPro != false){
            $array["receiver"]["activity_id"] = $this->receiverActivity;
        }
        
        return $array;
    }


    /**
     * Set the value of fromCountry
     *
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
     * @return  self
     */ 
    public function setShipmentType($shipmentType)
    {
        $this->shipmentType = $shipmentType;

        return $this;
    }

    /**
     * Set the value of senderPro
     *
     * @return  self
     */ 
    public function setSenderPro($senderPro)
    {
        $this->senderPro = $senderPro;

        return $this;
    }

    /**
     * Set the value of senderProRevenue
     *
     * @return  self
     */ 
    public function setSenderProRevenue($senderProRevenue)
    {
        $this->senderProRevenue = $senderProRevenue;

        return $this;
    }

    /**
     * Set the value of senderProRevenueCurrency
     *
     * @return  self
     */ 
    public function setSenderProRevenueCurrency($senderProRevenueCurrency)
    {
        $this->senderProRevenueCurrency = $senderProRevenueCurrency;

        return $this;
    }

    /**
     * Set the value of transportType
     *
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
     * @return  self
     */ 
    public function setTransportCarrier($transportCarrier)
    {
        $this->transportCarrier = $transportCarrier;

        return $this;
    }

    /**
     * Set the value of receiverPro
     *
     * @return  self
     */ 
    public function setReceiverPro($receiverPro)
    {
        $this->receiverPro = $receiverPro;

        return $this;
    }

    /**
     * Set the value of receiverActivity
     *
     * @return  self
     */ 
    public function setReceiverActivity($receiverActivity)
    {
        $this->receiverActivity = $receiverActivity;

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