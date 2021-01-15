<?php

namespace Transiteo\Taxes\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Transiteo\Base\Model\TransiteoApiService;
use Transiteo\Base\Model\TransiteoApiShipmentParameters;

class TransiteoProducts
{
    private $apiService;
    private $apiResponseContent;
    private $serializer;
    private $productsParams;
    private $shipmentParams;

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

        return $this;
    }

    /**
     * Get the value of apiService
     */
    public function getApiService()
    {
        return $this->apiService;
    }

    public function getDuties()
    {
        $finalParams = [];
        foreach ($this->productsParams as $param) {
            $finalParams['products'][] = $param->buildArray();
        }

        $finalParams = array_merge($finalParams, $this->shipmentParams->buildArray());
        $this->apiResponseContent = json_decode(($this->apiService->getDuties($finalParams)), true);

        //////////////////LOGGER//////////////
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/response_product.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        ob_start();
        var_dump($this->apiResponseContent["products"]);
        $result = ob_get_clean();
        $logger->info($result);
        ///////////////////////////////////////

        //set products ids as keys for results products
        if (isset($this->apiResponseContent["products"])&& isset($this->productsParams)) {
            $this->apiResponseContent["products"] = \array_combine(\array_keys($this->productsParams), $this->apiResponseContent["products"]);
        }
        return true;
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
        if ($this->apiResponseContent == null) {
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
        if ($this->apiResponseContent == null) {
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
     * Get Vat By Product ID
     *
     * @param $productId
     * @return int|mixed|null
     */
    public function getVat($productId)
    {
        if ($this->apiResponseContent == null) {
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
        if ($this->apiResponseContent == null) {
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
        if ($this->apiResponseContent == null) {
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
        if ($this->apiResponseContent == null) {
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
        if ($this->apiResponseContent == null) {
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
        if ($this->apiResponseContent == null) {
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
        if ($this->apiResponseContent == null) {
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
        if ($this->apiResponseContent == null) {
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
        if ($this->apiResponseContent == null) {
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
        if ($this->apiResponseContent == null) {
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
        if ($this->apiResponseContent == null) {
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
