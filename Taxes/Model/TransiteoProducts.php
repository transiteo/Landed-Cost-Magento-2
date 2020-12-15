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
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        ob_start();
        var_dump($this->apiResponseContent);
        $result = ob_get_clean();
        $logger->info($result);
        ///////////////////////////////////////
//        if (isset($this->apiResponseContent->httpCode) && $this->apiResponseContent->httpCode != 200) {
//            return $this->apiResponseContent;
//        } else {
//            return true;
//        }

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

        $totalTax = 0;
        if (isset($this->apiResponseContent["duty_fees_global"])) {
            $totalTax += ($this->apiResponseContent["duty_fees_global"] ?? 0);
        }
        return $totalTax;
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

        $totalTax = 0;
        if (isset($this->apiResponseContent["products"])) {
            $totalTax += ($this->apiResponseContent["products"][$productId]["duty"]["product_taxes_amount"] ?? 0);
            $totalTax += ($this->apiResponseContent["products"][$productId]["duty"]["vat_taxes_amount"] ?? 0);
            $totalTax += ($this->apiResponseContent["products"][$productId]["duty"]["shipping_taxes_amount"] ?? 0);
        } else {
            return null;
        }

        return $totalTax;
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

        $totalTax = 0;
        if (isset($this->apiResponseContent["products"])) {
            if (isset($this->apiResponseContent["products"][$productId]["vat"])) {
                foreach (($this->apiResponseContent["products"][$productId]["vat"]) as $vat) {
                    $totalTax += ($vat["product_taxes_amount"] ?? 0);
                    $totalTax += ($vat["shipping_taxes_amount"] ?? 0);
                }
            }
        } else {
            return null;
        }

        return $totalTax;
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
        if (isset($this->apiResponseContent["products"])) {
            if (isset($this->apiResponseContent["products"][$productId]["vat"])) {
                foreach (($this->apiResponseContent["products"][$productId]["vat"]) as $vat) {
                    if (isset($vat["label"])) {
                        $labels[] = $vat["label"];
                    }
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

        if (isset($this->apiResponseContent["products"])) {
            return $this->apiResponseContent["products"][$productId]["vat"]["percentage"] ?? null;
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

        $totalTax = 0;
        if (isset($this->apiResponseContent["products"])) {
            if (isset($this->apiResponseContent["products"][$productId]["special_taxes"]["product_taxes_amount"])) {
                $totalTax += ($this->apiResponseContent["products"][$productId]["special_taxes"]["product_taxes_amount"] ?? 0);
            }
        } else {
            return null;
        }
        return $totalTax;
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

        $totalTax = 0;
        if (isset($this->apiResponseContent["shipping_global"])) {
            $totalTax += ($this->apiResponseContent["shipping_global"]["duty"]["amount"] ?? 0);
            $totalTax += ($this->apiResponseContent["shipping_global"]["duty"]["vat_amount"] ?? 0);
        }
        return $totalTax;
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

        $totalTax = 0;
        if (isset($this->apiResponseContent["shipping_global"]["vat"])) {
            foreach ($this->apiResponseContent["shipping_global"]["vat"] as $vat) {
                $totalTax += ($vat["amount"] ?? 0);
            }
        }
        return $totalTax;
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

        $totalTax = 0;
        if (isset($this->apiResponseContent["shipping_global"])) {
            if (isset($this->apiResponseContent["products"]["special_taxes"]["amount"])) {
                $totalTax += ($this->apiResponseContent["products"]["special_taxes"]["amount"] ?? 0);
            }
        } else {
            return null;
        }
        return $totalTax;
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
        $totalTax = 0;
        if (isset($this->apiResponseContent["products"])) {
            foreach ($this->apiResponseContent["products"] as $product) {
                $totalTax += ($product["duty"]["product_taxes_amount"] ?? 0);
                $totalTax += ($product["duty"]["vat_taxes_amount"] ?? 0);
                $totalTax += ($product["duty"]["shipping_taxes_amount"] ?? 0);
            }
        } else {
            return null;
        }
        $totalTax += $this->getShippingDuty();
        $totalTax += $this->getDutyFeesGlobal(); //@todo ?? IS it at the right place ?

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
        $totalTax = 0;
        if (isset($this->apiResponseContent["products"])) {
            foreach ($this->apiResponseContent["products"] as $product) {
                if (isset($product["vat"])) {
                    foreach (($product["vat"]) as $vat) {
                        $totalTax += ($vat["product_taxes_amount"] ?? 0);
                        $totalTax += ($vat["shipping_taxes_amount"] ?? 0);
                    }
                }
            }
        } else {
            return null;
        }
        $totalTax += $this->getShippingVat();

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

        $totalTax = 0;
        if (isset($this->apiResponseContent["products"])) {
            foreach ($this->apiResponseContent["products"] as $product) {
                if (isset($product["special_taxes"]["product_taxes_amount"])) {
                    $totalTax += ($product["special_taxes"]["product_taxes_amount"] ?? 0);
                }
            }
        } else {
            return null;
        }
        $totalTax += $this->getShippingSpecialTaxes();
        return $totalTax;
    }

    /**
     * Return total taxes amount
     *
     * @return mixed|null
     */
    public function getTotalTaxes()
    {
        if ($this->apiResponseContent == null) {
            $response = $this->getDuties();
            if ($response !== true) {
                return null;
            }
        }

        if (isset($this->apiResponseContent['global'])) {
            $total = $this->apiResponseContent['global']['amount'];
        } else {
            return null;
        }

        return $total;
    }
}
