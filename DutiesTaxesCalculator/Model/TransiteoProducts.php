<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\DutiesTaxesCalculator\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Transiteo\DutiesTaxesCalculator\Model\TransiteoApiService;
use Transiteo\DutiesTaxesCalculator\Model\TransiteoApiShipmentParameters;

class TransiteoProducts
{
    private $apiService;
    private $apiResponseContent;
    private $serializer;
    private $productsParams;
    private $shipmentParams;
    private $responseIsOk;
    private $isProductsInitialized = false;
    private $isShipmentInitialized = false;
    private $getDutiesCalled = false;

    public function __construct(
        TransiteoApiService $apiService,
        TransiteoApiShipmentParameters $shipmentParams,
        SerializerInterface $serializer
    ) {
        $this->apiService = $apiService;
        $this->shipmentParams = $shipmentParams;
        $this->serializer = $serializer;
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
        foreach ($this->productsParams as $param) {
            $finalParams['products'][] = $param->buildArray();
        }

        $finalParams = array_merge($finalParams, $this->shipmentParams->buildArray());
        $this->apiResponseContent = \json_decode(($this->getDutiesFromApi($finalParams)), true);

        //set products ids as keys for results products
        if (isset($this->apiResponseContent["products"])&& isset($this->productsParams)) {
            $this->apiResponseContent["products"] = \array_combine(\array_keys($this->productsParams), $this->apiResponseContent["products"]);
            $this->responseIsOk = true;
        } else {
            $this->responseIsOk = false;
            return false;
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
        ob_start();
        var_dump($request);
        $result = ob_get_clean();
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
                ob_start();
                var_dump($responseArray);
                $result = ob_get_clean();
                $this->apiService->getLogger()->debug('Response Content : ' . $result);
            }
        } else {
            if (array_key_exists('message', $responseArray)) {
                ////LOGGER////
                $this->apiService->getLogger()->debug('Response : status => ' . $status . ' message : ' . $responseArray['message']);
            }
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

        if (isset($this->apiResponseContent["products"][$productId]["duty"])) {
            $totalTax = 0;
            $totalTax += ($this->apiResponseContent["products"][$productId]["duty"]["product_taxes_amount"] ?? 0);
            $totalTax += ($this->apiResponseContent["products"][$productId]["duty"]["vat_taxes_amount"] ?? 0);
            $totalTax += ($this->apiResponseContent["products"][$productId]["duty"]["shipping_taxes_amount"] ?? 0);
            return $totalTax;
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
            return $this->productsParams[$productId]->getUnitPrice() + $this->getTotalTaxes($productId);
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
            $taxes = $this->getTotalTaxes($productId) ?? 0;
            $price = $this->getProductPriceWithoutTaxes($productId);
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

        if (isset($this->apiResponseContent["products"][$productId]["vat"])) {
            $totalTax = 0;
            foreach (($this->apiResponseContent["products"][$productId]["vat"]) as $vat) {
                $totalTax += ($vat["product_taxes_amount"] ?? 0);
                $totalTax += ($vat["shipping_taxes_amount"] ?? 0);
            }
            return $totalTax;
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

        if (isset($this->apiResponseContent["products"])
        && isset($this->apiResponseContent["products"][$productId])
        && isset($this->apiResponseContent["products"][$productId]["special_taxes"])
        ) {
            $totalTax = 0;
            $totalTax += ($this->apiResponseContent["products"][$productId]["special_taxes"]["special_taxes_amount"] ?? 0);
            return $totalTax;
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
            foreach ($this->apiResponseContent["products"] as $product) {
                if (isset($product["duty"])) {
                    $isNull &= $this->safeSum($totalTax, $product["duty"]["product_taxes_amount"] ?? null);
                    $isNull &= $this->safeSum($totalTax, $product["duty"]["vat_taxes_amount"] ?? null);
                    $isNull &= $this->safeSum($totalTax, $product["duty"]["shipping_taxes_amount"] ?? null);
                }
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
            foreach ($this->apiResponseContent["products"] as $product) {
                if (isset($product["vat"])) {
                    foreach (($product["vat"]) as $vat) {
                        $isNull &= $this->safeSum($totalTax, $vat["product_taxes_amount"] ?? null);
                        $isNull &= $this->safeSum($totalTax, $vat["shipping_taxes_amount"] ?? null);
                    }
                }
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
            foreach ($this->apiResponseContent["products"] as $product) {
                if (isset($product["special_taxes"])) {
                    foreach (($product["special_taxes"]) as $specialTaxes) {
                        $isNull &= $this->safeSum($totalTax, $specialTaxes["product_taxes_amount"] ?? null);
                        $isNull &= $this->safeSum($totalTax, $specialTaxes["shipping_taxes_amount"] ?? null);
                        $isNull &= $this->safeSum($totalTax, $specialTaxes["vat_taxes_amount"] ?? null);
                    }
                }
            }
        }

        $isNull &= $this->safeSum($totalTax, $this->getShippingVat());

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
