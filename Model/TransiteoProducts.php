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

use Magento\Framework\Webapi\Rest\Request;
use Transiteo\LandedCost\Model\Cache\Handler\Taxes;

class TransiteoProducts
{
    public const FALLBACK_DUTY_PERCENT = 0.06;

    /**
     * @var TransiteoApiService
     */
    protected $apiService;

    /**
     * @var array
     */
    protected $apiResponseContent;
    /**
     * @var TransiteoApiProductParameters[]
     */
    protected $productsParams;
    /**
     * @var TransiteoApiShipmentParameters
     */
    protected $shipmentParams;

    /**
     * @var
     */
    protected $responseIsOk;
    /**
     * @var bool
     */
    protected $isProductsInitialized = false;
    /**
     * @var bool
     */
    protected $isShipmentInitialized = false;

    /**
     * @var bool
     */
    protected $transiteoApiCalled = false;
    /**
     * @var Taxes
     */
    protected $taxesCacheHandler;

    /**
     * @param TransiteoApiService $apiService
     * @param TransiteoApiShipmentParameters $shipmentParams
     * @param Taxes $taxesCacheHandler
     */
    public function __construct(
        TransiteoApiService $apiService,
        TransiteoApiShipmentParameters $shipmentParams,
        Taxes $taxesCacheHandler
    ) {
        $this->apiService = $apiService;
        $this->shipmentParams = $shipmentParams;
        $this->taxesCacheHandler = $taxesCacheHandler;
    }

    /**
     * Set the value of all products' params
     *
     * @param $products
     * @return  self
     */
    public function setProducts($products)
    {
        $this->productsParams = $products;
        $this->isProductsInitialized = true;
        return $this;
    }

    /**
     * Set the value of shipmentParams
     *
     * @param $shipmentParams
     * @return  self
     */
    public function setShipmentParams($shipmentParams)
    {
        $this->shipmentParams = $shipmentParams;
        $this->isShipmentInitialized = true;

        return $this;
    }

    /**
     * Get the value of apiService
     */
    public function getApiService()
    {
        return $this->apiService;
    }

    /**
     * Return True if Shipment and Products param are set and Response is valid
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->responseIsOk && $this->isProductsInitialized && $this->isShipmentInitialized;
    }

    public function callTransiteoApi()
    {
        if ($this->transiteoApiCalled || !($this->isProductsInitialized && $this->isShipmentInitialized)) {
            return false;
        }
        $this->transiteoApiCalled = true;
        $finalParams = [];
        foreach ($this->productsParams as $id => $param) {
            $finalParams['products'][] = $param->buildArray();
        }

        $finalParams = array_merge($finalParams, $this->shipmentParams->buildArray());

        $cacheKey = $this->taxesCacheHandler->getKeyFromRequest($finalParams);
        $cachedTaxes = $this->taxesCacheHandler->loadFromCache($cacheKey);
        if(!isset($cachedTaxes)){
            $this->apiService->getLogger()->debug('Requesting to API :');
            $this->apiResponseContent = \json_decode(($this->getDutiesFromApi($finalParams)), true);

            if(array_key_exists(0, $this->apiResponseContent ?? [])){
                $this->apiResponseContent = $this->apiResponseContent[0];
            }
            //set products ids as keys for results products
            if (isset($this->apiResponseContent["products"])&& isset($this->productsParams)) {
                $this->apiResponseContent["products"] = \array_combine(\array_keys($this->productsParams), $this->apiResponseContent["products"]);
                $this->responseIsOk = true;
                $this->taxesCacheHandler->storeToCache($cacheKey,$this->apiResponseContent,
                    array_map(function ($param) {
                        return $param->getId();
                    }, $this->productsParams)
                );
            } else {
                $this->taxesCacheHandler->removeFromCache($cacheKey);
                $this->responseIsOk = false;
                return false;
            }
        }else{
            $this->apiResponseContent = $cachedTaxes;
            $this->apiService->getLogger()->debug('Loading from cache '. $cacheKey . ' result :' . \json_encode($cachedTaxes));
            $this->responseIsOk = true;
        }

        return true;
    }

    /**
     * Get Duties for a designated product
     */
    public function getDutiesFromApi($productsParams)
    {
        $request = [
            'headers' => [
                'Content-type'     => 'application/json',
                'Authorization' => $this->apiService->getIdToken(),
            ],
            'json' => $productsParams
        ];

        //////////////////LOGGER//////////////
        $result = \json_encode($request);
        $this->apiService->getLogger()->debug("Request : " . $result);
        ///////////////////////////////////////

        $response = $this->apiService->doRequest(
            TransiteoApiService::API_REQUEST_URI . "v1/taxsrv/dutyCalculation",
            $request,
            Request::HTTP_METHOD_POST
        );

        $status = $response->getStatusCode();

        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents();

        $responseArray = \json_decode($responseContent);

        ///LOGGER///
        $this->apiService->getLogger()->debug('Response : status => ' . ($status ?? 'null') . ' message : ' . $response->getReasonPhrase());

        if ($status == "200") {
            if (isset($responseArray)) {
                ////LOGGER////
                $result = \json_encode($responseArray);
                $this->apiService->getLogger()->debug('Response Content : ' . $result);
            }
        } else {
            if (is_array($responseArray) && array_key_exists('message', $responseArray)) {
                $message = $responseArray['message'];
                ////LOGGER////
            }else{
                $message = $response->getReasonPhrase();
            }
            $this->apiService->getLogger()->debug('Response : status => ' . $status . ' message : ' . $message);
        }

        if ($status == "401") {
            if (isset($responseArray->message) && $responseArray->message == "The incoming token has expired") {
                $this->apiService->refreshIdToken();
                $this->getDutiesFromApi($productsParams);
            }
        }

        return $responseContent;
    }

    /**
     * Clear results to again to api
     */
    public function clearResults()
    {
        $this->apiResponseContent = null;
    }

    /**
     * Clear everything to make a new call
     */
    public function clearAll()
    {
        $this->productsParams = null;
        $this->shipmentParams = null;
    }

    /**
     * Get Duty by Product ID
     *
     * @param $productId
     * @return int|mixed|null
     */
    public function getDuty($productId)
    {
        if (!$this->isValid()) {
            $this->callTransiteoApi();
        }

        $isNull = true;
        $total = 0.0;
        if (isset($this->apiResponseContent["products"][$productId]["duty"])) {
            $isNull &= $this->safeSum($total, $this->apiResponseContent["products"][$productId]["duty"]["product_taxes_amount"] ?? null);
            $isNull &= $this->safeSum($total, $this->apiResponseContent["products"][$productId]["duty"]["vat_taxes_amount"] ?? null);
            $isNull &= $this->safeSum($total, $this->apiResponseContent["products"][$productId]["duty"]["shipping_taxes_amount"] ?? null);
            $isNull &= $this->safeSum($total, $this->apiResponseContent["products"][$productId]["duty"]["packaging_taxes_amount"] ?? null);
            $isNull &= $this->safeSum($total, $this->apiResponseContent["products"][$productId]["duty"]["insurance_taxes_amount"] ?? null);
        }else if(!$this->responseIsOk){
            ////LOGGER////
            $product = $this->productsParams[$productId];
            $isNull &= $this->safeSum($total,(($product->getUnitPrice() + $product->getUnitShipPrice()) * self::FALLBACK_DUTY_PERCENT * $product->getQuantity()));
            $this->apiService->getLogger()->debug(sprintf("Invalid reponse from API, use fallback value of %s percent for product %s: %s", self::FALLBACK_DUTY_PERCENT, $productId, $total));
        }

        if(!$isNull){
            return $total;
        }

        return null;
    }

    /**
     * Get Vat By Product ID
     *
     * @param $productId
     * @return int|mixed|null
     */
    public function getVat($productId)
    {
        if (!$this->isValid()) {
            $response = $this->callTransiteoApi();
            if ($response !== true) {
                return null;
            }
        }

        $isNull = true;
        $total = 0.0;
        if (isset($this->apiResponseContent["products"][$productId]["vat"])) {
            foreach (($this->apiResponseContent["products"][$productId]["vat"]) as $vat) {
                $isNull &= $this->safeSum($total, $vat["product_taxes_amount"] ?? null);
                $isNull &= $this->safeSum($total, $vat["shipping_taxes_amount"] ?? null);
                $isNull &= $this->safeSum($total, $vat["packaging_taxes_amount"] ?? null);
                $isNull &= $this->safeSum($total, $vat["insurance_taxes_amount"] ?? null);
            }
        }
        if(!$isNull){
            return $total;
        }

        return null;
    }

    /**
     * Get Special Taxes by product Id
     *
     * @param $productId
     * @return int|null
     */
    public function getSpecialTaxes($productId)
    {
        if (!$this->isValid()) {
            $response = $this->callTransiteoApi();
            if ($response !== true) {
                return null;
            }
        }

        $isNull = true;
        $total = 0.0;
        if (isset($this->apiResponseContent["products"])
        && isset($this->apiResponseContent["products"][$productId])
        && isset($this->apiResponseContent["products"][$productId]["special_taxes"])
        ) {
            foreach (($this->apiResponseContent["products"][$productId]["special_taxes"]) as $specialTaxes) {
                $isNull &= $this->safeSum($total, $specialTaxes["product_taxes_amount"] ?? null);
                $isNull &= $this->safeSum($total, $specialTaxes["shipping_taxes_amount"] ?? null);
                $isNull &= $this->safeSum($total, $specialTaxes["packaging_taxes_amount"] ?? null);
                $isNull &= $this->safeSum($total, $specialTaxes["insurance_taxes_amount"] ?? null);
                $isNull &= $this->safeSum($total, $specialTaxes["special_taxes_amount"] ?? null);
            }
        }
        if(!$isNull){
            return $total;
        }
        return null;
    }

    /**
     * Add safely first element to second one, return true is value was null
     *
     * @param $totalTaxes
     * @param $value
     * @return bool
     */
    protected function safeSum(&$sum, $value)
    {
        if (isset($value)) {
            $sum += $value;
            return false;
        }
        return true;
    }

    /**
     * @return int|mixed|null
     */
    public function getTotalDuty()
    {
        if (!$this->isValid()) {
            $this->callTransiteoApi();
        }
        return $this->apiResponseContent["global"]["amount_duty"] ?? null;
    }

    /**
     * Return Total Vat
     *
     * @return int|mixed|null
     */
    public function getTotalVat()
    {
        if (!$this->isValid()) {
            $response = $this->callTransiteoApi();
            if ($response !== true) {
                return null;
            }
        }
        return $this->apiResponseContent["global"]["amount_vat"] ?? null;
    }

    /**
     * Return Total Vat
     *
     * @return int|mixed|null
     */
    public function getTotalExtraFees()
    {
        if (!$this->isValid()) {
            $response = $this->callTransiteoApi();
            if ($response !== true) {
                return null;
            }
        }

        if(!isset($this->apiResponseContent["extra_fees"])){
            return null;
        }

        return $this->apiResponseContent["extra_fees"]["amount"] ?? null;
    }



    /**
     *
     * Return Total Special Taxes
     *
     * @return int|mixed|null
     */
    public function getTotalSpecialTaxes()
    {
        if (!$this->isValid()) {
            $response = $this->callTransiteoApi();
            if ($response !== true) {
                return null;
            }
        }
        return $this->apiResponseContent["global"]["amount_special_taxes"] ?? null;
    }

    /**
     * Return total taxes amount for a product if a product id is passed or for all tes products
     *
     * @param int|null $productId
     * @return mixed|null
     */
    public function getTotalTaxes($productId = null)
    {
        if (!$this->isValid()) {
            $this->callTransiteoApi();
        }


        $isNull = true;
        $total = 0;

        // Return fallback duty if response is not ok.
        if(!$this->responseIsOk){
            if($productId !== null){
                return $this->getDuty($productId);
            }
            return $this->getTotalDuty();
        }

        if ($productId !== null) {
            $isNull &= $this->safeSum($total, $this->getDuty($productId));
            $isNull &= $this->safeSum($total, $this->getVat($productId));
            $isNull &= $this->safeSum($total, $this->getSpecialTaxes($productId));
            if ($isNull) {
                return null;
            }

            return $total;
        }

        return $this->apiResponseContent["global"]["amount_duty_and_tax"] ?? null;
    }

    /**
     * @param int|null $productId
     * @return float|null
     */
    public function getGrandTotal($productId = null){
        if (!$this->isValid()) {
            $this->callTransiteoApi();
        }
        if($productId !== null){
            return $this->apiResponseContent["products"][$productId]["amount_total"] ?? null;
        }
        return $this->apiResponseContent["global"]["amount_total"] ?? null;
    }

    /**
     * @param int|null $productId
     * @return float|null
     */
    public function getSubtotalExclusiveVAT($productId = null){
        if (!$this->isValid()) {
            $this->callTransiteoApi();
        }
        if($productId !== null){
            return $this->apiResponseContent["products"][$productId]["amount_exclusive"] ?? null;
        }
        return $this->apiResponseContent["global"]["amount_exclusive"] ?? null;
    }

    /**
     * @param int|null $productId
     * @return float|null
     */
    public function getSubtotalInclusiveVAT($productId = null){
        if (!$this->isValid()) {
            $this->callTransiteoApi();
        }
        if($productId !== null){
            return $this->apiResponseContent["products"][$productId]["amount_inclusive_vat"] ?? null;
        }
        return $this->apiResponseContent["global"]["amount_inclusive_vat"] ?? null;
    }

    public function getSubtotalInclusiveTaxes($productId = null){
        if (!$this->isValid()) {
            $this->callTransiteoApi();
        }
        if($productId !== null){
            return $this->getSubtotalExclusiveVAT($productId) + $this->getTotalTaxes($productId);
        }
        return $this->getSubtotalExclusiveVAT() + $this->getTotalTaxes();
    }


    /**
     * @param int|null $productId
     * @return float|null
     */
    public function getPercentageTotalTaxes($productId = null){
        if (!$this->isValid()) {
            $this->callTransiteoApi();
        }
        if($productId !== null){

            $isNull = true;
            $total = 0;

            if(isset($this->apiResponseContent["products"][$productId]["duty"]) && is_array($this->apiResponseContent["products"][$productId]["duty"])){
                foreach ($this->apiResponseContent["products"][$productId]["duty"] as $duty){
                    $isNull &= $this->safeSum($total,$duty["percentage"] ?? null);
                }
            }else{
                $isNull &= $this->safeSum($total,$this->apiResponseContent["products"][$productId]["duty"]["percentage"] ?? null);
            }
            if(isset($this->apiResponseContent["products"][$productId]["special_taxes"]) && is_array($this->apiResponseContent["products"][$productId]["special_taxes"])){
                foreach ($this->apiResponseContent["products"][$productId]["special_taxes"] as $special_taxes){
                    $isNull &= $this->safeSum($total,$special_taxes["percentage"] ?? null);
                }
            }else{
                $isNull &= $this->safeSum($total,$this->apiResponseContent["products"][$productId]["special_taxes"]["percentage"] ?? null);
            }

            if(isset($this->apiResponseContent["products"][$productId]["vat"]) && is_array($this->apiResponseContent["products"][$productId]["vat"])){
                foreach ($this->apiResponseContent["products"][$productId]["vat"] as $vat){
                    $isNull &= $this->safeSum($total,$vat["percentage"] ?? null);
                }
            }else{
                $isNull &= $this->safeSum($total,$this->apiResponseContent["products"][$productId]["vat"]["percentage"] ?? null);
            }

            if ($isNull) {
                return null;
            }
            return $total;
        }
        return $this->apiResponseContent["global"]["percentage_duty_and_tax"] ?? null;
    }

    /**
     * @return string|null
     */
    public function getVatLabel(){
        if (!$this->isValid()) {
            $this->callTransiteoApi();
        }

        if(isset($this->apiResponseContent["products"])){
            foreach ($this->apiResponseContent["products"] as $product){
                if(is_array($product["vat"] ?? null)){
                    foreach ($product["vat"] as $vat){
                        if(($vat["label"] ?? null) !== null){
                            return $vat["label"];
                        }
                    }
                }else{
                    if(isset($product["vat"]["label"])){
                        return $product["vat"]["label"];
                    }
                }
            }
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function getSpecialTaxesLabel(){
        if (!$this->isValid()) {
            $this->callTransiteoApi();
        }
        if(isset($this->apiResponseContent["products"])){
            foreach ($this->apiResponseContent["products"] as $product){
                if(is_array($product["special_taxes"] ?? null)){
                    foreach ($product["special_taxes"] as $special_tax){
                        if(($special_tax["label"] ?? null) !== null){
                            return $special_tax["label"];
                        }
                    }
                }else{
                    if(isset($product["special_taxes"]["label"])){
                        return $product["special_taxes"]["label"];
                    }
                }
            }
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function getDutyLabel(){
        if (!$this->isValid()) {
            $this->callTransiteoApi();
        }
        if(isset($this->apiResponseContent["products"])){
            foreach ($this->apiResponseContent["products"] as $product){
                if(is_array($product["duty"] ?? null)){
                    foreach ($product["duty"] as $duty){
                        if(($duty["label"] ?? null) !== null){
                            return $duty["label"];
                        }
                    }
                }else{
                    if(isset($product["duty"]["label"])){
                        return $product["duty"]["label"];
                    }
                }
            }
        }
        return null;
    }


    /**
     * @return string|null
     */
    public function getTotalTaxesLabel(){
        return __("Total Duties And Taxes")->render();
    }

    /**
     * @return string|null
     */
    public function getExtraFeesLabel()
    {
        if (!$this->isValid()) {
            $this->callTransiteoApi();
        }
        if(isset($this->apiResponseContent["extra_fees"])){
            if(isset($this->apiResponseContent["extra_fees"]["label"])){
                return $this->apiResponseContent["extra_fees"]["label"];
            }
        }
        return null;
    }
}
