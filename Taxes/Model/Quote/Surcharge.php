<?php

/**
 * @author Bruno FACHE <bruno@bird.eu>
 * @copyright Copyright (c) 2020 Blackbird ((https://black.bird.eu))
 * @link (https://black.bird.eu)
 */

namespace Transiteo\Taxes\Model\Quote;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Transiteo\Taxes\Service\TaxesService;

class Surcharge extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    const COLLECTOR_TYPE_CODE = 'transiteo-duty-taxes';

    protected $vat;
    protected $duty;
    protected $specialTaxes;
    protected $totalTaxes;

    /**
     * @var TaxesService
     */
    protected $taxexService;

    /**
     * Custom constructor.
     */
    public function __construct(
        TaxesService $taxesService
    ) {
        $this->taxexService = $taxesService;
        $this->setCode(self::COLLECTOR_TYPE_CODE);
    }

    /**
     * Collect address discount amount
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }

        $this->getTransiteoTaxes($quote, $shippingAssignment);

        $amount = 0;
        //Getting total Taxes Amount previously recorded in quote
        $amount += $this->totalTaxes;

        $total->setCustomAmount($amount);
        $total->setBaseCustomAmount($amount);
        $total->setTotalAmount(self::COLLECTOR_TYPE_CODE, $amount);
        $total->setBaseTotalAmount(self::COLLECTOR_TYPE_CODE, $amount);
        $total->setGrandTotal($total->getGrandTotal() + $amount);
        $total->setBaseGrandTotal($total->getBaseGrandTotal() + $amount);
        return $this;
    }

    /**
     * @param Total $total
     */
    protected function clearValues(Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount(self::COLLECTOR_TYPE_CODE, 0);
        $total->setBaseTotalAmount(self::COLLECTOR_TYPE_CODE, 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }

    /**
     * @param Quote $quote
     * @param Total $total
     * @return array
     */
    public function fetch(
        Quote $quote,
        Total $total
    ) {
        $amount = 0;

        foreach ($quote->getItemsCollection() as $_quoteItem) {
            $amount += $_quoteItem->getQty() * 0;
        }

        $this->vat = 2;
        $this->duty = 3;
        $this->specialTaxes = 5;
        $this->totalTaxes = 10;
        $this->getTransiteoTaxes($quote);
        $amount = $this->totalTaxes;
        //Recording duties in quote
        $this->saveInQuote($quote);

        return [
            'code' => $this->getCode(),
            'title' => __('CrossBorder Duty and Taxes'),
            'value' => $amount
        ];
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('CrossBorder Duty and Taxes');
    }

    /**
     * Save Transiteo data in quote
     *
     * @param $duty
     * @param $vat
     * @param $specialTaxes
     */
    protected function saveInQuote($quote)
    {
        $quote->setTransiteoDuty($this->duty);
        $quote->setTransiteoVat($this->vat);
        $quote->setTransiteoSpecialTaxes($this->specialTaxes);
        $quote->setTransiteoTotalTaxes($this->totalTaxes);
    }

    /**
     * Retrieve Transiteo Taxes
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface|null $shippingAssignment
     */
    protected function getTransiteoTaxes($quote, $shippingAssignment = null)
    {
        /**
         * @var \Magento\Quote\Api\Data\CartItemInterface $quoteItem
         */
        $products = [];
        foreach ($quote->getItemsCollection() as $quoteItem) {
            if ($quoteItem->getHasChildren()) {
                $id = $quoteItem->getItemId();
                $amount = $quoteItem->getQty();
                $products[$id] = $amount;
            }
        }

        $params = [];
        //getShippingAmount
        $shippingAmount = $quote->getShippingAmount();
        if (!isset($shippingAmount)) {
            $params[TaxesService::SHIPPING_AMOUNT] =  0;
        } else {
            $params[TaxesService::SHIPPING_AMOUNT] = $shippingAmount;
        }
        /**
         * TODO
         * Get Customer pro and activity
         */

        /** TODO Ensure that country is given on checkout */
        if ($shippingAssignment) {
            $countryId = $shippingAssignment->getShipping()->getAddress()->getCountryId();
            if ($countryId) {
                $params[TaxesService::TO_COUNTRY] = $countryId;
            }
            $districtId = $shippingAssignment->getShipping()->getAddress()->getRegionCode();
            if ($districtId) {
                $params[TaxesService::TO_DISTRICT] = $districtId;
            }
        }
        //get duties and taxes from taxes service
        $taxes= $this->taxexService->getDutiesByProducts($products, $params);

        //get duties
        $this->duty = $taxes[TaxesService::RETURN_KEY_DUTY];
        $this->vat =$taxes[TaxesService::RETURN_KEY_VAT];
        $this->specialTaxes = $taxes[TaxesService::RETURN_KEY_SPECIAL_TAXES];
        $this->totalTaxes = $taxes[TaxesService::RETURN_KEY_TOTAL_TAXES];
    }
}
