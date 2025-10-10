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
declare(strict_types=1);

namespace Transiteo\LandedCost\Model\Quote;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Item;
use Transiteo\LandedCost\Model\TransiteoProducts;
use Transiteo\LandedCost\Service\TaxesService;

class Surcharge extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    const COLLECTOR_TYPE_CODE = 'transiteo-duty-taxes';

    const FALLBACK_DUTY_PERCENT = 0.06;
    const FALLBACK_VAT_PERCENT = 0.20;
    /**
     * @var TaxesService
     */
    protected $taxesService;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Custom constructor.
     */
    public function __construct(
        TaxesService                            $taxesService,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->request = $request;
        $this->taxesService = $taxesService;
        $this->setCode(self::COLLECTOR_TYPE_CODE);
    }

    /**
     * Collect address discount amount
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function collect(
        Quote                       $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total                       $total
    )
    {
        // If module disabled, skip
        if (!$this->taxesService->getConfig()->isEnabled()) {
            return $this;
        }

        $quote->setTransiteoDisplay(false);
        parent::collect($quote, $shippingAssignment, $total);

        if ($quote->getItemsQty() === 0) {
            return $this;
        }

        //Get Items from shippingAssignment or from quote
        if (isset($shippingAssignment)) {
            $items = $shippingAssignment->getItems();
        }
        if (empty($items)) {
            $items = $quote->getItemsCollection()->getItems();
        }

        if (!isset($items) || empty($items)
            || (is_array($items) && reset($items) === null)) {
            return $this;
        }

        foreach ($items as $item) {
            $item->setData(TaxesService::ITEM_IDENTIFIER_KEY, $item->getProduct()?->getId() . $item->getQty());
        }

        $this->applyDiscountToQuoteItems($quote, $items);

        $isCalculationFromCheckout = $this->isCalculatingFromCheckout();
        if (($isCalculationFromCheckout && $this->taxesService->isActivatedOnCheckout()) ||
            (!$isCalculationFromCheckout && $this->taxesService->isActivatedOnCartView())
        ) {
            //Save current quote and quote items data
            $quoteData = $quote->getData();
            $itemsData = [];
            foreach ($items as $item) {
                $itemsData[$item->getData(TaxesService::ITEM_IDENTIFIER_KEY)] = $item->getData();
            }
            $totalData = $total->getData();

            try {
                $quote->setTransiteoDisplay(true);
                $transiteoProducts = $this->getTransiteoTaxes($quote, $total, $items, $shippingAssignment);
                $this->applyTransiteoDutiesAndTaxesToQuoteItems($quote, $items, $transiteoProducts);
                $this->applyTransiteoDutiesAndTaxesToQuote($total, $quote, $transiteoProducts);
                $this->applyDutiesAndTaxesToTotal($total, $quote, $transiteoProducts);
            } catch (\Throwable $throwable) {
                //Restore Quote and items data
                foreach ($items as $item) {
                    $item->setData($itemsData[$item->getData(TaxesService::ITEM_IDENTIFIER_KEY)]);
                }
                $quote->setData($quoteData);
                $total->setData($totalData);

                //////////////////LOGGER//////////////
                $this->taxesService->getLogger()->error($throwable->getMessage());
                //  /////////////////////////////////////

                try{
                    $this->applyFallbackDutyAndTaxesToQuoteItems($quote, $items);
                    $this->applyFallbackDutyAndTaxesToQuote($quote, $total);
                    $this->applyDutiesAndTaxesToTotal($total, $quote, null);
                }catch (\Throwable $throwable){
                    # In case fallback crash, do not apply any calculation

                    //////////////////LOGGER//////////////
                    $this->taxesService->getLogger()->error($throwable->getMessage());
                    //  /////////////////////////////////////
                    foreach ($items as $item) {
                        $item->setData($itemsData[$item->getData(TaxesService::ITEM_IDENTIFIER_KEY)]);
                    }
                    $quote->setData($quoteData);
                    $total->setData($totalData);
                }

                return $this;
            }
        }

        return $this;
    }

    /**
     * @param Total $total
     * @param Quote $quote
     * @param TransiteoProducts|null $transiteoProducts
     * @return void
     */
    protected function fillTotalAppliedTaxes(Total $total, Quote $quote, ?TransiteoProducts $transiteoProducts = null)
    {
        // Populate applied_taxes
        $appliedTaxes = [];

        $vat = $quote->getTransiteoVat();
        if ($vat !== null) {
            $appliedTaxes[] = [
                'percent' => 100,
                'amount' => $vat,
                'rates' => [
                    [
                        'title' => $transiteoProducts?->getVatLabel() ?? __('VAT')->render(),
                        'percent' => 100
                    ]
                ]
            ];
        }

        $duty = $quote->getTransiteoDuty();
        if ($duty !== null) {
            $appliedTaxes[] = [
                'percent' => 100,
                'amount' => $duty,
                'rates' => [
                    [
                        'title' => $transiteoProducts?->getDutyLabel() ?? __('Duty')->render(),
                        'percent' => 100
                    ]
                ]
            ];
        }

        $specialTaxes = $quote->getTransiteoSpecialTaxes();
        if ($specialTaxes !== null) {
            $appliedTaxes[] = [
                'percent' => 100,
                'amount' => $specialTaxes,
                'rates' => [
                    [
                        'title' => $transiteoProducts?->getSpecialTaxesLabel() ?? __('Special Taxes')->render(),
                        'percent' => 100
                    ]
                ]
            ];
        }

        if (isset($transiteoProducts)) {
            $extraFees = $transiteoProducts->getTotalExtraFees();
            if ($extraFees !== null) {
                $appliedTaxes[] = [
                    'percent' => 100,
                    'amount' => $extraFees,
                    'rates' => [
                        [
                            'title' => $transiteoProducts?->getExtraFeesLabel() ?? __('Extra Fees')->render(),
                            'percent' => 100
                        ]
                    ]
                ];
            }
        }

        $totalTaxes = $quote->getTransiteoTotalTaxes();
        if (!empty($totalTaxes)) {
            $appliedTaxes[] = [
                'percent' => 100,
                'amount' => $totalTaxes,
                'rates' => [
                    [
                        'title' => $transiteoProducts?->getTotalTaxesLabel() ?? __('Total Taxes')->render(),
                        'percent' => 100
                    ]
                ]
            ];
        }


        $total->setData('applied_taxes', $appliedTaxes);
    }

    /**
     * @return bool
     */
    protected function isCalculatingFromCheckout()
    {
        $controllerName = $this->request->getControllerName();
        if ($controllerName === "cart") {
            return false;
        }
        return true;
    }

    /**
     * @param Quote $quote
     * @param Total $total
     * @return array
     */
    public function fetch(
        Quote $quote,
        Total $total
    )
    {
        if (!$this->taxesService->getConfig()->isEnabled()) {
            return [
                'code' => $this->getCode(),
                'title' => __('Duty & Taxes'),
                'value' => null,
                'base_value' => null,
            ];
        }
        $isCheckoutCart = $this->isCalculatingFromCheckout();
        if (($isCheckoutCart && $this->taxesService->isActivatedOnCheckout()) ||
            (!$isCheckoutCart && $this->taxesService->isActivatedOnCartView())
        ) {
            try {
                $quote->setTransiteoDisplay(true);
                $quote->save();
            } catch (\Exception $e) {
                //////////////////LOGGER//////////////
                $this->taxesService->getLogger()->error($e->getMessage());
                ///////////////////////////////////////
            }
        }

        if ($this->taxesService->isDDPActivated()) {
            $included = ' ' . __('(included)');
        } else {
            $included = ' ' . __('(not included)');
        }
        /**
         * @todo Clean ??
         */
        $included = "";
        return [
            'code' => $this->getCode(),
            'title' => __('Duty & Taxes') . $included,
            'value' => $total->getData('transiteo-duty-taxes_amount'),
            'base_value' => $total->getData('base_transiteo-duty-taxes_amount'),
        ];
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Duty & Taxes');
    }

    /**
     * @param Total $total
     * @param Quote $quote
     * @param TransiteoProducts $transiteoProducts
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function applyTransiteoDutiesAndTaxesToQuote(Total $total, Quote $quote, TransiteoProducts $transiteoProducts)
    {
        $duty = $transiteoProducts->getTotalDuty();
        $vat = $transiteoProducts->getTotalVat();
        $specialTaxes = $transiteoProducts->getTotalSpecialTaxes();
        $totalTaxes = $transiteoProducts->getTotalTaxes();
        $subtotal = $transiteoProducts->getSubtotalExclusiveVAT();
        $subtotalInclTaxes = $transiteoProducts->getGrandTotal();

        $this->applyDutiesAndTaxesToQuote($quote, $total, $duty, $vat, $specialTaxes, $totalTaxes, $subtotal, $subtotalInclTaxes);
    }

    /**
     * @param $quote
     * @param $total
     * @param $items
     * @param $shippingAssignment
     * @return TransiteoProducts
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getTransiteoTaxes($quote, $total,$items, $shippingAssignment = null): \Transiteo\LandedCost\Model\TransiteoProducts
    {
        ////LOGGER////
        $this->taxesService->getLogger()->debug('Request for quoteID => ' . ($quote->getId() ?? '') . ' ' . ($quote->getCustomerEmail() ?? ''));
        /**
         * @var \Magento\Quote\Api\Data\CartItemInterface $quoteItem
         */
        $products = [];

        foreach ($items as $quoteItem) {
            if ($quoteItem->getParentItem()) {
                continue;
            }

            $id = $quoteItem->getProduct()->getId();
            $products[$id] = $quoteItem;
        }

        $params = [];
        //getShippingAmount
        $shippingAmount = $total->getShippingAmount();
        if (!isset($shippingAmount)) {
            $params[TaxesService::SHIPPING_AMOUNT] = 0;
        } else {
            $params[TaxesService::SHIPPING_AMOUNT] = $shippingAmount;
        }
        $params[TaxesService::OBJECT_TOTAL] = $total;
        $params[TaxesService::OBJECT_SHIPPING_ASSIGNEMENT] = $shippingAssignment;

        /**
         * TODO
         * Get Customer pro and activity
         */

        //Get address from shipping assignment, if address type is shipping of from billing if there is no shipping address registered
        if ($shippingAssignment && ($quote->getShippingAddress() && $shippingAssignment?->getShipping()?->getAddress()?->getAddressType() !== "billing")) {
            $countryId = $shippingAssignment->getShipping()->getAddress()->getCountryId();
            $districtId = $shippingAssignment->getShipping()->getAddress()->getRegionCode();
        } else {
            $countryId = $quote->getShippingAddress()?->getCountryId();
            $districtId = $quote->getShippingAddress()?->getRegionCode();
            if (!$countryId) {
                $countryId = $quote->getBillingAddress()?->getCountryId();
                $districtId = $quote->getBillingAddress()?->getRegionCode();
            }
        }

        if ($countryId) {
            $params[TaxesService::TO_COUNTRY] = $countryId;
            if ($districtId) {
                $districtId = $countryId . '-' . $districtId;
                //If country is us, append postcode to id
                if ($countryId === "US") {
                    $zip = $shippingAssignment->getShipping()->getAddress()->getPostcode();
                    if ($zip) {
                        $districtId .= '-' . $zip;
                        $params[TaxesService::TO_DISTRICT] = $districtId;
                    }
                } else {
                    $params[TaxesService::TO_DISTRICT] = $districtId;
                }
            } else {
                $districtId = $shippingAssignment->getShipping()->getAddress()->getRegion();
                if ($districtId) {
                    $params[TaxesService::TO_DISTRICT] = $districtId;
                }
            }
        }


        if ($products === []) {
            throw new \Exception('Product Cart is Empty from Transiteo Api.');
        }

        //get duties and taxes from taxes service
        $taxes = $this->taxesService->getDutiesByQuoteItems($products, $params);

        if (
            !array_key_exists(TaxesService::RETURN_KEY_PRODUCTS, $taxes) ||
            !array_key_exists(TaxesService::RETURN_KEY_VAT, $taxes) ||
            !array_key_exists(TaxesService::RETURN_KEY_SPECIAL_TAXES, $taxes) ||
            !array_key_exists(TaxesService::RETURN_KEY_TOTAL_TAXES, $taxes)
        ) {
            throw new \Exception('Unable to get Duty and Taxes from Transiteo Api.');
        }

        //////////////////LOGGER//////////////
        $this->taxesService->getLogger()->debug(
            'Result for quoteID => ' . ($quote->getId() ?? '') . ' ' . ($quote->getCustomerEmail() ?? '') . ' : ' .
            ',Duty => ' . ($taxes[TaxesService::RETURN_KEY_DUTY] ?? 'null') .
            ' ,VAT => ' . ($taxes[TaxesService::RETURN_KEY_VAT] ?? 'null') .
            ' ,SPECIAL TAXES => ' . ($taxes[TaxesService::RETURN_KEY_SPECIAL_TAXES] ?? 'null') .
            ' ,TOTAL TAXES => ' . ($taxes[TaxesService::RETURN_KEY_TOTAL_TAXES] ?? 'null')
        );
        ///////////////////////////////////////

        return $taxes[TaxesService::RETURN_KEY_PRODUCTS];
    }

    /**
     * @param Quote $quote
     * @param Item[] $items
     * @param TransiteoProducts $transiteoProducts
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function applyTransiteoDutiesAndTaxesToQuoteItems(Quote $quote, array $quoteItems, TransiteoProducts $transiteoProducts): void
    {
        foreach ($quoteItems as $quoteItem) {
            if($quoteItem->getParentItem()){
                continue;
            }
            /**
             * @var ProductInterface $product
             */
            $id = (int) $quoteItem->getData(TaxesService::ITEM_IDENTIFIER_KEY);

            $duty = $transiteoProducts->getDuty($id);
            $specialTaxes = $transiteoProducts->getSpecialTaxes($id);
            $totalTaxes = $transiteoProducts->getTotalTaxes($id);
            $vatAmount = $transiteoProducts->getVat($id);
            $rowTotalIncludingTaxes = $transiteoProducts->getGrandTotal($id) ?? 0.0;
            $rowTotal = $transiteoProducts->getSubtotalExclusiveVAT($id);
            $taxPercent = $transiteoProducts->getPercentageTotalTaxes($id) ?? 0.0;

            $this->applyDutiesAndTaxesToQuoteItems($quoteItem, $vatAmount, $duty, $specialTaxes, $totalTaxes, $taxPercent, $rowTotalIncludingTaxes, $rowTotal);
        }
    }

    /**
     * @param Quote $quote
     * @param CartItemInterface[] $quoteItems
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function applyFallbackDutyAndTaxesToQuoteItems(Quote $quote, array $quoteItems): void
    {
        /**
         * @var \Magento\Quote\Model\Quote\Item[] $quoteItems
         */
        foreach ($quoteItems as $quoteItem) {
            if($quoteItem->getParentItem()){
                continue;
            }
            if($quoteItem->getCustomPrice()){
                $price = $quoteItem->getCustomPrice();
            }else{
                $price = (float) $quoteItem->getPrice();
                $quoteItem->setCustomPrice($price);
            }
            if(!$quoteItem->getNoDiscount()){
                $price -= ($quoteItem->getDeltaDiscount() ?? 0.0);
            }
            $qty = $quoteItem->getQty();
            $rowSubtotal = $price * $qty;
            $specialTaxes = null;
            $vatAmount = $rowSubtotal - ($rowSubtotal / (1.0 + self::FALLBACK_VAT_PERCENT));
            $taxPercent = self::FALLBACK_DUTY_PERCENT + self::FALLBACK_VAT_PERCENT;
            $rowTotal = $rowSubtotal - $vatAmount;
            $duty = $rowTotal * self::FALLBACK_DUTY_PERCENT;
            $totalTaxes = $duty + $vatAmount;
            $rowTotalIncludingTaxes = $rowTotal + $totalTaxes;
            $this->applyDutiesAndTaxesToQuoteItems($quoteItem, $vatAmount, $duty, $specialTaxes, $totalTaxes, $taxPercent, $rowTotalIncludingTaxes, $rowTotal);

        }
    }

    /**
     * @param CartItemInterface $quoteItem
     * @param float|null $vatAmount
     * @param float|null $duty
     * @param float|null $specialTaxes
     * @param float|null $totalTaxes
     * @param float $taxPercent
     * @param float $rowTotalIncludingTaxes
     * @param float $rowTotal
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function applyDutiesAndTaxesToQuoteItems(CartItemInterface $quoteItem, ?float $vatAmount, ?float $duty, ?float $specialTaxes, ?float $totalTaxes, float $taxPercent, float $rowTotalIncludingTaxes, float $rowTotal): void
    {
        //Set Transiteo Taxes
        $quoteItem->setData('transiteo_vat', $vatAmount);
        $quoteItem->setData('transiteo_duty', $duty);
        $quoteItem->setData('transiteo_special_taxes', $specialTaxes);
        $quoteItem->setData('transiteo_total_taxes', $totalTaxes);

        $currencyRate = $this->taxesService->getCurrentCurrencyRate();
        if (isset($vatAmount)) {
            $quoteItem->setData('base_transiteo_vat', $vatAmount / $currencyRate);
        } else {
            $quoteItem->setData('base_transiteo_vat', null);
        }

        if (isset($duty)) {
            $quoteItem->setData('base_transiteo_duty', $duty / $currencyRate);
        } else {
            $quoteItem->setData('base_transiteo_duty', null);
        }

        if (isset($specialTaxes)) {
            $quoteItem->setData('base_transiteo_special_taxes', $specialTaxes / $currencyRate);
        } else {
            $quoteItem->setData('base_transiteo_special_taxes', null);
        }
        if (isset($totalTaxes)) {
            $quoteItem->setData('base_transiteo_total_taxes', $totalTaxes / $currencyRate);
        } else {
            $quoteItem->setData('base_transiteo_total_taxes', null);
        }

        //if taxes have been retrieved
        if (isset($totalTaxes)) {
            $quoteItem->setTaxAmount($totalTaxes ?? 0);
            $quoteItem->setBaseTaxAmount($totalTaxes / $currencyRate);
            $quoteItem->setTaxPercent($taxPercent ?? 0);

            $quoteItem->setRowTotalInclTax($rowTotalIncludingTaxes);
            $quoteItem->setBaseRowTotalInclTax($rowTotalIncludingTaxes / $currencyRate);
            $quoteItem->setRowTotalWithDiscount($rowTotalIncludingTaxes);

            $unitPrice = round(($rowTotal ?? 0) / $quoteItem->getQty(), 2);
            $quoteItem->setPrice($unitPrice);
            $quoteItem->setCalculationPrice($unitPrice);
            $quoteItem->setBasePrice($unitPrice / $currencyRate);
            $quoteItem->setRowTotal($rowTotal);
            $quoteItem->setBaseRowTotal($rowTotal / $currencyRate);


            $unitPriceInclTax = round(($rowTotalIncludingTaxes ?? 0) / $quoteItem->getQty(), 2);
            $quoteItem->setPriceInclTax($unitPriceInclTax);
            $quoteItem->setBasePriceInclTax($unitPriceInclTax / $currencyRate);
        }
    }

    /**
     * @param Quote $quote
     * @param Total $total
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function applyFallbackDutyAndTaxesToQuote(Quote $quote, Total $total): void
    {
        $subtotalInclVat = (float) $total->getSubtotal();
        $vatAmount = $subtotalInclVat - ($subtotalInclVat / (1.0 + self::FALLBACK_VAT_PERCENT));
        $subtotal = $subtotalInclVat - $vatAmount;
        $duty = $subtotal * self::FALLBACK_DUTY_PERCENT;
        $specialTaxes = 0.0;
        $totalTaxes = $duty + $vatAmount + $specialTaxes;
        $subtotalInclTaxes = $subtotal + $totalTaxes;

        $this->applyDutiesAndTaxesToQuote($quote, $total, $duty, $vatAmount, $specialTaxes, $totalTaxes, $subtotal, $subtotalInclTaxes);
        $message = sprintf("Error during price retrieval, using fallback duty percentage of %s and vat %s => %s for quote %s %s", self::FALLBACK_DUTY_PERCENT, self::FALLBACK_VAT_PERCENT, $totalTaxes, $quote->getId(), $quote->getCustomerEmail() ?? '');
        $quote->addMessage($message);
        $this->taxesService->getLogger()->info($message);
    }

    /**
     * @param Total $total
     * @param Quote $quote
     * @param TransiteoProducts|null $transiteoProducts
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function applyDutiesAndTaxesToTotal(Total $total, Quote $quote, ?TransiteoProducts $transiteoProducts = null): void
    {
        $total->setTransiteoDuty($quote->getTransiteoDuty());
        $total->setBaseTransiteoDuty($quote->getBaseTransiteoDuty());
        $total->setTransiteoVat($quote->getTransiteoVat());
        $total->setBaseTransiteoVat($quote->getBaseTransiteoVat());

        $total->setTransiteoSpecialTaxes($quote->getTransiteoSpecialTaxes());
        $total->setBaseTransiteoSpecialTaxes($quote->getBaseTransiteoSpecialTaxes());

        $total->setTransiteoTotalTaxes($quote->getTransiteoTotalTaxes());
        $total->setBaseTransiteoTotalTaxes($quote->getBaseTransiteoTotalTaxes());

        if (isset($transiteoProducts)) {
            $subtotalInclusiveTaxes = $transiteoProducts->getSubtotalInclusiveTaxes();
        } else {
            $subtotalInclusiveTaxes = $quote->getSubtotal() + $quote->getTransiteoTotalTaxes();
        }
        $total->setSubtotalInclTax($subtotalInclusiveTaxes);
        $total->setBaseSubtotalInclTax($subtotalInclusiveTaxes / $this->taxesService->getCurrentCurrencyRate());
        $total->setTotalAmount(self::COLLECTOR_TYPE_CODE, $quote->getTransiteoTotalTaxes());
        $total->setBaseTotalAmount(self::COLLECTOR_TYPE_CODE, $quote->getBaseTransiteoTotalTaxes());

        //Report data from quote :
        $total->setSubtotal($quote->getSubtotal());
        $total->setBaseSubtotal($quote->getBaseSubtotal());
        $total->setSubtotalWithDiscount($quote->getSubtotalWithDiscount());
        $total->setBaseSubtotalWithDiscount($quote->getBaseSubtotalWithDiscount());
        $total->setGrandTotal($quote->getGrandTotal());
        $total->setBaseGrandTotal($quote->getBaseGrandTotal());

        $this->fillTotalAppliedTaxes($total, $quote, $transiteoProducts);
    }

    /**
     * @param Quote $quote
     * @param Total $total
     * @param float|null $duty
     * @param float|null $vat
     * @param float|null $specialTaxes
     * @param float|null $totalTaxes
     * @param float $subtotal
     * @param float $subtotalInclTaxes
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function applyDutiesAndTaxesToQuote(Quote $quote, Total $total, ?float $duty, ?float $vat, ?float $specialTaxes, ?float $totalTaxes, float $subtotal, float $subtotalInclTaxes): void
    {
        $quote->setTransiteoIncoterm($this->taxesService->getIncoterm());
        $currencyRate = $this->taxesService->getCurrentCurrencyRate();

        $quote->setTransiteoDuty($duty);
        if (isset($duty)) {
            $quote->setBaseTransiteoDuty($duty / $currencyRate);
        } else {
            $quote->setBaseTransiteoDuty(null);
        }

        $quote->setTransiteoVat($vat);
        if (isset($vat)) {
            $quote->setBaseTransiteoVat($vat / $currencyRate);
        } else {
            $quote->setBaseTransiteoVat(null);
        }

        $quote->setTransiteoSpecialTaxes($specialTaxes);
        if (isset($specialTaxes)) {
            $quote->setBaseTransiteoSpecialTaxes($specialTaxes / $currencyRate);
        } else {
            $quote->setBaseTransiteoSpecialTaxes(null);
        }

        $quote->setTransiteoTotalTaxes($totalTaxes);
        if (isset($totalTaxes)) {
            $quote->setBaseTransiteoTotalTaxes($totalTaxes / $currencyRate);
        } else {
            $quote->setBaseTransiteoTotalTaxes(null);
        }

        $discountAmount = $quote->getBaseSubtotalWithDiscount() - $quote->getSubtotal();
        $quote->setSubtotal($subtotal);
        $quote->setBaseSubtotal($subtotal / $currencyRate);

        $subtotalWithDiscount = $subtotal + $discountAmount;
        $quote->setSubtotalWithDiscount($subtotalWithDiscount);
        $quote->setBaseSubtotalWithDiscount($subtotalWithDiscount / $currencyRate);

        $grandTotal = $subtotalInclTaxes + $total->getShippingAmount();
        $quote->setGrandTotal($grandTotal);
        $quote->setBaseGrandTotal($grandTotal / $currencyRate);
    }

    /**
     * @param Quote $quote
     * @param array $products
     * @return void
     */
    public function applyDiscountToQuoteItems(Quote $quote, array $products): void
    {
        $globalDiscountAmount = $quote->getSubtotal() - $quote->getSubtotalWithDiscount();
        if ($globalDiscountAmount > 0.0) {

            //calculate if there is a global discount
            foreach ($products as $product) {
                if($product->getParentItem()){
                    continue;
                }
                $globalDiscountAmount -= $product->getDiscountAmount();
            }
            $qty = $quote->getItemsQty();
            if ($qty > 0) {
                //calculate the global discount delta to apply on each products
                $globalDelta = $globalDiscountAmount / $qty;
                foreach ($products as $product) {
                    if($product->getParentItem()){
                        continue;
                    }
                    //calculate the order row discount delta to apply
                    $discountAmount = $product->getDiscountAmount();
                    $qty = $product->getQty();
                    $delta = ($discountAmount / $qty) + $globalDelta;
                    //used to calculate the price to send request to transiteo
                    $product->setDeltaDiscount($delta);
                }
            }
        }
    }
}
