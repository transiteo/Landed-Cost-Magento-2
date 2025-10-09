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

use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Transiteo\LandedCost\Model\TransiteoProducts;
use Transiteo\LandedCost\Service\TaxesService;

class Surcharge extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    const COLLECTOR_TYPE_CODE = 'transiteo-duty-taxes';

    const FALLBACK_DUTY_PERCENT = 0.06;
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
        TaxesService $taxesService,
        \Magento\Framework\App\RequestInterface $request
    ) {
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
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        // If module disabled, skip
        if (!$this->taxesService->getConfig()->isEnabled()) {
            return $this;
        }

        $quote->setTransiteoDisplay(false);
        parent::collect($quote, $shippingAssignment, $total);

        if($quote->getItemsQty() === 0){
            return $this;
        }

        if (isset($shippingAssignment)) {
            $items = $shippingAssignment->getItems();
            if (empty($items)) {
                $items = $quote->getItemsCollection();
            }
        } else {
            $items = $quote->getItemsCollection()->getItems();
        }
        if(!isset($items) || empty($items)
            || (is_object($items) && $items->getFirstItem()->getRowTotal()) === null
            || (is_array($items) && reset($items) === null)) {
            return $this;
        }

        $amount = 0;
        $isCheckoutCart = $this->manageCheckoutState();
        if (($isCheckoutCart && $this->taxesService->isActivatedOnCheckout()) ||
            (!$isCheckoutCart && $this->taxesService->isActivatedOnCartView())
        ) {
            $quoteData = $quote->getData();
            $connection = $quote->getResource()->getConnection();
            $beginTransaction = false;
            try {
                $quote->setTransiteoDisplay(true);
                $transiteoProducts = $this->getTransiteoTaxes($quote, $total, $shippingAssignment);
                $connection->beginTransaction();
                $beginTransaction = true;
                //Recording duties in quote
                $this->applyDutiesAndTaxesToQuote($total, $quote, $transiteoProducts);
                $this->applyDutiesAndTaxesToTotal($total, $quote, $transiteoProducts);
                $connection->commit();
            } catch (\Throwable $exception) {
                if($beginTransaction){
                    $connection->rollBack();
                    $quote->setData($quoteData);
                }
                //////////////////LOGGER//////////////
                $this->taxesService->getLogger()->error($exception->getMessage());
                //  /////////////////////////////////////

                $this->applyFallbackDutyToQuote($quote, $total);
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
    protected function fillTotalAppliedTaxes(Total $total, Quote $quote, ?TransiteoProducts $transiteoProducts = null){
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
                        'title' => $transiteoProducts?->getDutyLabel() ??  __('Duty')->render(),
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

        if(isset($transiteoProducts)){
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
                        'title' => $transiteoProducts?->getTotalTaxesLabel() ?? __('Special Taxes')->render(),
                        'percent' => 100
                    ]
                ]
            ];
        }


        $total->setData('applied_taxes', $appliedTaxes);
    }

    /**
     * ManageCheckoutState, Save Checkout state and retrieve current is in checkout value
     *
     * @return boolean
     */
    protected function manageCheckoutState()
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
    ) {
        if (!$this->taxesService->getConfig()->isEnabled()) {
            return [
                'code' => $this->getCode(),
                'title' => __('Duty & Taxes'),
                'value' => null,
                'base_value' => null,
            ];
        }
        $isCheckoutCart = $this->manageCheckoutState();
        if (($isCheckoutCart && $this->taxesService->isActivatedOnCheckout()) ||
            (!$isCheckoutCart && $this->taxesService->isActivatedOnCartView())
        ) {
            try {
                $quote->setTransiteoDisplay(true);
                $quote->save();
            }catch (\Exception $e){
                //////////////////LOGGER//////////////
                $this->taxesService->getLogger()->error($e->getMessage());
                ///////////////////////////////////////
            }
        }

        if($this->taxesService->isDDPActivated()){
            $included = ' ' . __('(included)');
        }else{
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
    protected function applyDutiesAndTaxesToQuote(Total $total,Quote $quote, TransiteoProducts $transiteoProducts)
    {
        $quote->setTransiteoIncoterm($this->taxesService->getIncoterm());
        $currencyRate = $this->taxesService->getCurrentCurrencyRate();

        $duty = $transiteoProducts->getTotalDuty();
        $quote->setTransiteoDuty($duty);
        if (isset($duty)) {
            $quote->setBaseTransiteoDuty($duty / $currencyRate);
        } else {
            $quote->setBaseTransiteoDuty(null);
        }

        $vat =  $transiteoProducts->getTotalVat();
        $quote->setTransiteoVat($vat);
        if (isset($vat)) {
            $quote->setBaseTransiteoVat($vat / $currencyRate);
        } else {
            $quote->setBaseTransiteoVat(null);
        }

        $specialTaxes = $transiteoProducts->getTotalSpecialTaxes();
        $quote->setTransiteoSpecialTaxes($specialTaxes);
        if (isset($specialTaxes)) {
            $quote->setBaseTransiteoSpecialTaxes($specialTaxes / $currencyRate);
        } else {
            $quote->setBaseTransiteoSpecialTaxes(null);
        }

        $totalTaxes = $transiteoProducts->getTotalTaxes();
        $quote->setTransiteoTotalTaxes($totalTaxes);
        if (isset($totalTaxes)) {
            $quote->setBaseTransiteoTotalTaxes($totalTaxes / $currencyRate);
        } else {
            $quote->setBaseTransiteoTotalTaxes(null);
        }

        $discountAmount =  $quote->getBaseSubtotalWithDiscount() - $quote->getSubtotal();
        $subtotal = $transiteoProducts->getSubtotalExclusiveVAT();
        $quote->setSubtotal($subtotal);
        $quote->setBaseSubtotal($subtotal / $currencyRate);

        $subtotalWithDiscount = $subtotal + $discountAmount;
        $quote->setSubtotalWithDiscount($subtotalWithDiscount);
        $quote->setBaseSubtotalWithDiscount($subtotalWithDiscount / $currencyRate);

        $grandTotal = $transiteoProducts->getGrandTotal() + $total->getShippingAmount();
        $quote->setGrandTotal($grandTotal);
        $quote->setBaseGrandTotal($grandTotal / $currencyRate);


        //Avoid error in graphql when saving quote directly
        $quoteResource = $quote->getResource();
        $connection = $quoteResource->getConnection();
        $table = $quoteResource->getMainTable();

        $data = [
            'transiteo_incoterm' => $quote->getTransiteoIncoterm(),
            'base_transiteo_total_taxes' => $quote->getBaseTransiteoTotalTaxes(),
            'transiteo_total_taxes' => $quote->getTransiteoTotalTaxes(),
            'base_transiteo_duty' => $quote->getBaseTransiteoDuty(),
            'transiteo_duty' => $quote->getTransiteoDuty(),
            'base_transiteo_vat' => $quote->getBaseTransiteoVat(),
            'transiteo_vat' => $quote->getTransiteoVat(),
            'base_transiteo_special_taxes' => $quote->getBaseTransiteoSpecialTaxes(),
            'transiteo_special_taxes' => $quote->getTransiteoSpecialTaxes(),
            'subtotal' => $quote->getSubtotal(),
            'base_subtotal' => $quote->getBaseSubtotal(),
            'subtotal_with_discount' => $quote->getSubtotalWithDiscount(),
            'base_subtotal_with_discount' => $quote->getBaseSubtotalWithDiscount(),
            'grand_total' => $quote->getGrandTotal(),
            'base_grand_total' => $quote->getBaseGrandTotal(),
        ];

        $connection->update(
            $table,
            $data,
            ['entity_id = ?' => $quote->getId()]
        );
    }

    /**
     * Retrieve Transiteo Taxes
     *
     * @param Quote $quote
     * @param Total $total
     * @param ShippingAssignmentInterface|null $shippingAssignment
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getTransiteoTaxes($quote, $total, $shippingAssignment = null): \Transiteo\LandedCost\Model\TransiteoProducts
    {
        ////LOGGER////
        $this->taxesService->getLogger()->debug('Request for quoteID => ' . ($quote->getId() ?? '') . ' ' . ($quote->getCustomerEmail() ?? ''));
        /**
         * @var \Magento\Quote\Api\Data\CartItemInterface $quoteItem
         */
        $products = [];
        if ($shippingAssignment) {
            $items = $shippingAssignment->getItems();
            if (count($items)>0) {
                //we are on checkout
                if ($quote->getIsCheckoutCart()) {
                    $params[TaxesService::DISALLOW_GET_COUNTRY_FROM_COOKIE] = true;
                }
            } else {
                $items = $quote->getItemsCollection();
            }
        } else {
            $items = $quote->getItemsCollection();
        }

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
            $params[TaxesService::SHIPPING_AMOUNT] =  0;
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
        if ($shippingAssignment && ($quote->getShippingAddress() && $shippingAssignment?->getShipping()?->getAddress()?->getAddressType() !== "billing" ) ) {
            $countryId = $shippingAssignment->getShipping()->getAddress()->getCountryId();
            $districtId = $shippingAssignment->getShipping()->getAddress()->getRegionCode();
        }else{
            $countryId = $quote->getShippingAddress()?->getCountryId();
            $districtId = $quote->getShippingAddress()?->getRegionCode();
            if(!$countryId){
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




        if ($products !== []) {
            // Apply discount to quoteItems
            $globalDiscountAmount = $quote->getSubtotal() - $quote->getSubtotalWithDiscount();
            if($globalDiscountAmount > 0.0){

                //calculate if there is a global discount
                foreach ($products as $product){
                    $globalDiscountAmount -= $product->getDiscountAmount();
                }


                $qty = $quote->getItemsQty();
                if($qty > 0){
                    //calculate the global discount delta to apply on each products
                    $globalDelta = $globalDiscountAmount / $qty;
                    foreach ($products as $product){
                        //calculate the order row discount delta to apply
                        $discountAmount = $product->getDiscountAmount();
                        $qty = $product->getQty();
                        $delta = ($discountAmount / $qty) + $globalDelta;
                        //used to calculate the price to send request to transiteo
                        $product->setDeltaDiscount($delta);
                    }
                }
            }

            //get duties and taxes from taxes service
            $taxes= $this->taxesService->getDutiesByQuoteItems($products, $params);

            //saving changes in products to quote
            $quote->setItems($products);
            //Avoid errors with graphql when saving quote
            foreach ($products as $product){
                $product->save();
            }
        } else {
            throw new \Exception('Product Cart is Empty from Transiteo Api.');
        }

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
            ' ,SPECIAL TAXES => ' . ($taxes[TaxesService::RETURN_KEY_SPECIAL_TAXES]  ?? 'null') .
            ' ,TOTAL TAXES => ' . ($taxes[TaxesService::RETURN_KEY_TOTAL_TAXES] ?? 'null')
        );
        ///////////////////////////////////////

        return $taxes[TaxesService::RETURN_KEY_PRODUCTS];
    }

    /**
     * @param Quote $quote
     * @param Total $total
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function applyFallbackDutyToQuote(Quote $quote, Total $total): void
    {
        $quote->setTransiteoIncoterm($this->taxesService->getIncoterm());
        $currencyRate = $this->taxesService->getCurrentCurrencyRate();
        $grandTotal = $total->getGrandTotal();
        $duty = $grandTotal * self::FALLBACK_DUTY_PERCENT;
        $message = sprintf("Error during price retrieval, using fallback duty percentage of %s => %s for quote %s %s", self::FALLBACK_DUTY_PERCENT, $duty, $quote->getId(), $quote->getCustomerEmail() ?? '');
        $quote->addMessage($message);
        $this->taxesService->getLogger()->info($message);
        $quote->setTransiteoDuty($duty);
        $quote->setBaseTransiteoDuty($duty / $currencyRate);;
        $quote->setTransiteoTotalTaxes($duty);
        $quote->setBaseTransiteoTotalTaxes($duty / $currencyRate);
        $quote->setGrandTotal($grandTotal + $duty);
        $quote->setBaseGrandTotal(($grandTotal + $duty) / $currencyRate);

        $quoteResource = $quote->getResource();
        $connection = $quoteResource->getConnection();
        $table = $quoteResource->getMainTable();

        $data = [
            'transiteo_incoterm' => $quote->getTransiteoIncoterm(),
            'base_transiteo_total_taxes' => $quote->getBaseTransiteoTotalTaxes(),
            'transiteo_total_taxes' => $quote->getTransiteoTotalTaxes(),
            'base_transiteo_duty' => $quote->getBaseTransiteoDuty(),
            'transiteo_duty' => $quote->getTransiteoDuty(),
            'grand_total' => $quote->getGrandTotal(),
            'base_grand_total' => $quote->getBaseGrandTotal(),
        ];

        $connection->update(
            $table,
            $data,
            ['entity_id = ?' => $quote->getId()]
        );
        $total->setTotalAmount(self::COLLECTOR_TYPE_CODE, $quote->getTransiteoTotalTaxes());
        $total->setBaseTotalAmount(self::COLLECTOR_TYPE_CODE, $quote->getBaseTransiteoTotalTaxes());
        $total->setTransiteoTotalTaxes($quote->getTransiteoTotalTaxes());
        $total->setBaseTransiteoTotalTaxes($quote->getBaseTransiteoTotalTaxes());
        $total->setTransiteoDuty($quote->getTransiteoDuty());
        $total->setBaseTransiteoDuty($quote->getBaseTransiteoDuty());
        $total->setGrandTotal($quote->getGrandTotal());
        $total->setBaseGrandTotal($quote->getBaseGrandTotal());
        $this->fillTotalAppliedTaxes($total, $quote);
    }

    /**
     * @param Total $total
     * @param Quote $quote
     * @param TransiteoProducts $transiteoProducts
     * @return void
     */
    protected function applyDutiesAndTaxesToTotal(Total $total, Quote $quote, TransiteoProducts $transiteoProducts): void
    {
        $total->setTransiteoDuty($quote->getTransiteoDuty());
        $total->setBaseTransiteoDuty($quote->getBaseTransiteoDuty());
        $total->setTransiteoVat($quote->getTransiteoVat());
        $total->setBaseTransiteoVat($quote->getBaseTransiteoVat());

        $total->setTransiteoSpecialTaxes($quote->getTransiteoSpecialTaxes());
        $total->setBaseTransiteoSpecialTaxes($quote->getBaseTransiteoSpecialTaxes());

        $total->setTransiteoTotalTaxes($quote->getTransiteoTotalTaxes());
        $total->setBaseTransiteoTotalTaxes($quote->getBaseTransiteoTotalTaxes());

        $subtotalInclusiveTaxes = $transiteoProducts->getSubtotalInclusiveTaxes();
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
}
