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
    protected $getDutiesCalled = false;
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

    public function getDuties()
    {
        if ($this->getDutiesCalled || !($this->isProductsInitialized && $this->isShipmentInitialized)) {
            return false;
        }
        $this->getDutiesCalled = true;
        $finalParams = [];
        $cacheParams = $this->shipmentParams->buildArrayForCache();
        foreach ($this->productsParams as $id => $param) {
            $finalParams['products'][] = $param->buildArray();
            $cacheParams[$id] = $param->builArrayForCache();
        }

        $finalParams = array_merge($finalParams, $this->shipmentParams->buildArray());

        $cacheKey = $this->taxesCacheHandler->getKeyFromRequest($cacheParams);
        $cachedTaxes = $this->taxesCacheHandler->loadFromCache($cacheKey);
        if(!isset($cachedTaxes)){
            $this->apiService->getLogger()->debug('Requesting to API :');
            $this->apiResponseContent = \json_decode(($this->getDutiesFromApi($finalParams)), true);
            //set products ids as keys for results products
            if (isset($this->apiResponseContent["products"])&& isset($this->productsParams)) {
                $this->apiResponseContent["products"] = \array_combine(\array_keys($this->productsParams), $this->apiResponseContent["products"]);
                $this->responseIsOk = true;
                $this->taxesCacheHandler->storeToCache($cacheKey,$this->apiResponseContent, array_keys($cacheParams));
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
     * Get Global Duty Fees
     *
     * @todo Implements get Duty Fees Global (verify if is correct)
     * @return int|mixed|null
     */
    public function getDutyFeesGlobal()
    {
        if (!$this->isValid()) {
            $response = $this->getDuties();
            if ($response !== true) {
                return null;
            }
        }

        return $this->apiResponseContent["duty_fees_global"] ?? null;
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
            $response = $this->getDuties();
            if ($response !== true) {
                return null;
            }
        }

        $isNull = true;
        $total = 0.0;
        if (isset($this->apiResponseContent["products"][$productId]["duty"])) {
            $isNull &= $this->safeSum($total, $this->apiResponseContent["products"][$productId]["duty"]["product_taxes_amount"] ?? null);
            $isNull &= $this->safeSum($total, $this->apiResponseContent["products"][$productId]["duty"]["vat_taxes_amount"] ?? null);
            $isNull &= $this->safeSum($total, $this->apiResponseContent["products"][$productId]["duty"]["shipping_taxes_amount"] ?? null);
            $isNull &= $this->safeSum($total, $this->apiResponseContent["products"][$productId]["duty"]["packaging_taxes_amount"] ?? null);
            $isNull &= $this->safeSum($total, $this->apiResponseContent["products"][$productId]["duty"]["insurance_taxes_amount"] ?? null);
        }
        if(!$isNull){
            return $total;
        }

        return null;
    }

    /**
     * @param int $productId
     * @return float|null
     */
    public function getProductPriceWithoutTaxes(int $productId):?float
    {
        if(array_key_exists($productId, $this->productsParams)){
            if($this->shipmentParams->isIncludedTaxes()){
                return $this->productsParams[$productId]->getUnitPrice() - ($this->getTotalTaxes($productId) / $this->productsParams[$productId]->getQuantity());
            }
            return $this->productsParams[$productId]->getUnitPrice();
        }
        return null;
    }

    /**
     * @param int $productId
     * @return float|null
     */
    public function getProductPriceInclTaxes(int $productId):?float
    {
        if(array_key_exists($productId, $this->productsParams)){
            if($this->shipmentParams->isIncludedTaxes()){
                return  $this->productsParams[$productId]->getUnitPrice();
            }
            return $this->productsParams[$productId]->getUnitPrice() + ($this->getTotalTaxes($productId) / $this->productsParams[$productId]->getQuantity());
        }
        return null;
    }

    /**
     * @param int $productId
     * @return float|null
     */
    public function getProductTaxPercent(int $productId):?float
    {
        if(array_key_exists($productId, $this->productsParams)){
            $price = $this->getProductPriceWithoutTaxes($productId);
            $taxes = $this->getProductPriceInclTaxes($productId) - $price;
            if($price === 0 || $price === null){
                $price = 1;
            }
            return ($taxes / $price) * 100;
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
            $response = $this->getDuties();
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
     * Get Vat Label By Product ID
     *
     * @param $productId
     * @return int|mixed|null
     */
    public function getVatLabels($productId)
    {
        if (!$this->isValid()) {
            $response = $this->getDuties();
            if ($response !== true) {
                return null;
            }
        }
        $labels = [];
        if (isset($this->apiResponseContent["products"][$productId]["vat"])) {
            foreach (($this->apiResponseContent["products"][$productId]["vat"]) as $vat) {
                if (isset($vat["label"])) {
                    $labels[] = $vat["label"];
                }
            }
        }
        return $labels;
    }

    /**
     * Return Vat Percentage
     * @param $productId
     * @return mixed|null
     */
    public function getVatPercentage($productId)
    {
        if (!$this->isValid()) {
            $response = $this->getDuties();
            if ($response !== true) {
                return null;
            }
        }

        if (isset($this->apiResponseContent["products"][$productId]["vat"])) {
            $totalPercentage = 0;
            foreach (($this->apiResponseContent["products"][$productId]["vat"]) as $vat) {
                $totalPercentage += ($vat["percentage"] ?? 0);
            }
            return $totalPercentage;
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
            $response = $this->getDuties();
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
     * Return total amount of shipping duty
     *
     * @return int|mixed|null
     */
    public function getShippingDuty()
    {
        if (!$this->isValid()) {
            $response = $this->getDuties();
            if ($response !== true) {
                return null;
            }
        }

        if (isset($this->apiResponseContent["shipping_global"]) && isset($this->apiResponseContent["shipping_global"]["duty"])) {
            $totalTax = 0;
            $totalTax += ($this->apiResponseContent["shipping_global"]["duty"]["amount"] ?? 0);
            $totalTax += ($this->apiResponseContent["shipping_global"]["duty"]["vat_amount"] ?? 0);
            return  $totalTax;
        }
        return null;
    }

    /**
     *
     * Return Shipping VAT
     *
     * @return int|mixed|null
     */
    public function getShippingVat()
    {
        if (!$this->isValid()) {
            $response = $this->getDuties();
            if ($response !== true) {
                return null;
            }
        }

        if (isset($this->apiResponseContent["shipping_global"])&& isset($this->apiResponseContent["shipping_global"]["vat"])) {
            $totalTax = 0;
            foreach ($this->apiResponseContent["shipping_global"]["vat"] as $vat) {
                $totalTax += ($vat["amount"] ?? 0);
            }
            return $totalTax;
        }
        return null;
    }

    /**
     * Get Shipping Special Taxes
     * @todo verify that it is correct
     * @return int|mixed|null
     */
    public function getShippingSpecialTaxes()
    {
        if (!$this->isValid()) {
            $response = $this->getDuties();
            if ($response !== true) {
                return null;
            }
        }

        if (isset($this->apiResponseContent["products"]) && isset($this->apiResponseContent["products"]["special_taxes"])) {
            $totalTax = 0;
            $totalTax += ($this->apiResponseContent["products"]["special_taxes"]["amount"] ?? 0);
            return $totalTax;
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
            $response = $this->getDuties();
            if ($response !== true) {
                return null;
            }
        }
        $isNull = true;
        $totalTax = 0;
        if (isset($this->apiResponseContent["products"])) {
            foreach ($this->apiResponseContent["products"] as $id => $product) {
                $isNull &= $this->safeSum($totalTax, $this->getDuty($id));
            }
        }

        //Get Shipping Duty
        $isNull &= $this->safeSum($totalTax, $this->getShippingDuty() ?? null);

        //Get Duty Fees Global
        $isNull &= $this->safeSum($totalTax, $this->getDutyFeesGlobal() ?? null);

        if ($isNull) {
            return null;
        }

        return $totalTax;
    }

    /**
     * Return Total Vat
     *
     * @return int|mixed|null
     */
    public function getTotalVat()
    {
        if (!$this->isValid()) {
            $response = $this->getDuties();
            if ($response !== true) {
                return null;
            }
        }
        $isNull = true;
        $totalTax = 0;
        if (isset($this->apiResponseContent["products"])) {
            foreach ($this->apiResponseContent["products"] as $id => $product) {
                $isNull &= $this->safeSum($totalTax, $this->getVat($id));
            }
        }

        $isNull &= $this->safeSum($totalTax, $this->getShippingVat());

        if ($isNull) {
            return null;
        }

        return $totalTax;
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
            $response = $this->getDuties();
            if ($response !== true) {
                return null;
            }
        }
        $isNull = true;
        $totalTax = 0;
        if (isset($this->apiResponseContent["products"])) {
            foreach ($this->apiResponseContent["products"] as $id => $product) {
                $isNull &= $this->safeSum($totalTax, $this->getSpecialTaxes($id));
            }
        }

        if ($isNull) {
            return null;
        }

        return $totalTax;
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
            $response = $this->getDuties();
            if ($response !== true) {
                return null;
            }
        }

        $isNull = true;
        $total = 0;
        if ($productId !== null) {
            $isNull &= $this->safeSum($total, $this->getDuty($productId));
            $isNull &= $this->safeSum($total, $this->getVat($productId));
            $isNull &= $this->safeSum($total, $this->getSpecialTaxes($productId));
        } else {
            $isNull &= $this->safeSum($total, $this->apiResponseContent['global']['amount'] ?? null);
        }

        if ($isNull) {
            return null;
        }

        return $total;
    }
}
