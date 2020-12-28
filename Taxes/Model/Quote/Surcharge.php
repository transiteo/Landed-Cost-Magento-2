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
        $amount = 0;
        $isCheckoutCart = $quote->getIsCheckoutCart();
        if (($isCheckoutCart && $this->taxexService->isActivatedOnCheckout()) ||
            (!$isCheckoutCart && $this->taxexService->isActivatedOnCartView())
        ) {
            try {
                $this->getTransiteoTaxes($quote, $total, $shippingAssignment);
                //Recording duties in quote
                $this->saveInQuote($quote);
                //Getting total Taxes Amount previously recorded in quote and add it to grand total if ddp is activated
                if ($this->taxexService->isDDPActivated()) {
                    $amount += $this->totalTaxes;
                }
            } catch (\Exception $exception) {
                //////////////////LOGGER//////////////
                $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);
                $logger->info('Exception raised' . $exception->getMessage());
                ///////////////////////////////////////
                $this->totalTaxes = 0;
            }
        }

        $total->setTransiteoDutyAmount($this->duty);
        $total->setBaseTransiteoDutyAmount($this->duty / $this->taxexService->getCurrentCurrencyRate());
        $total->setTransiteoVatAmount($this->vat);
        $total->setBaseTransiteoVatAmount($this->vat / $this->taxexService->getCurrentCurrencyRate());
        $total->setTransiteoSpecialTaxesAmount($this->specialTaxes);
        $total->setBaseTransiteoSpecialTaxesAmount($this->specialTaxes / $this->taxexService->getCurrentCurrencyRate());
        $total->setTransiteoTotalTaxesAmount($this->totalTaxes);
        $total->setBaseTransiteoTotalTaxesAmount($this->totalTaxes / $this->taxexService->getCurrentCurrencyRate());
        $total->setTotalAmount(self::COLLECTOR_TYPE_CODE, $amount);
        $total->setBaseTotalAmount(self::COLLECTOR_TYPE_CODE, $amount);
        $total->setGrandTotal($total->getGrandTotal() + $amount);
        $total->setBaseGrandTotal($total->getBaseGrandTotal() + ($amount / $this->taxexService->getCurrentCurrencyRate()));

        //////////////////LOGGER//////////////
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/surcharge.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('Collect amount :' . $amount . ', Grand Total' . $total->getGrandTotal() . ' Base Grand Total ' . $total->getBaseGrandTotal());
        ///////////////////////////////////////
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
        $total->setTransiteoDutyAmount(0);
        $total->setBaseTransiteoDutyAmount(0);
        $total->setTransiteoVatAmount(0);
        $total->setBaseTransiteoVatAmount(0);
        $total->setTransiteoSpecialTaxesAmount(0);
        $total->setBaseTransiteoSpecialTaxesAmount(0);
        $total->setTransiteoTotalTaxesAmount(0);
        $total->setBaseTransiteoTotalTaxesAmount(0);

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
        $isCheckoutCart = $quote->getIsCheckoutCart();
        if (($isCheckoutCart && $this->taxexService->isActivatedOnCheckout()) ||
            (!$isCheckoutCart && $this->taxexService->isActivatedOnCartView())
        ) {
            try {
                //Getting total Taxes Amount previously recorded in quote and add it to grand total if ddp is activated
                if ($this->taxexService->isDDPActivated()) {
                    $amount += $quote->getTransiteoTotalTaxesAmount();
                }
            } catch (\Exception $exception) {
                //////////////////LOGGER//////////////
                $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);
                $logger->info('Exception raised' . $exception->getMessage());
                ///////////////////////////////////////
                $this->totalTaxes =0;
            }
        }



        //////////////////LOGGER//////////////
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/surcharge.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('Fetch amount :' . $amount . ', Grand Total ' . $total->getGrandTotal() . ' Base Grand Total ' . $total->getBaseGrandTotal());
        ///////////////////////////////////////

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
        $quote->setBaseTransiteoDuty($this->duty / $this->taxexService->getCurrentCurrencyRate());
        $quote->setTransiteoVat($this->vat);
        $quote->setBaseTransiteoVat($this->vat / $this->taxexService->getCurrentCurrencyRate());
        $quote->setTransiteoSpecialTaxes($this->specialTaxes);
        $quote->setBaseTransiteoSpecialTaxes($this->specialTaxes / $this->taxexService->getCurrentCurrencyRate());
        $quote->setTransiteoTotalTaxes($this->totalTaxes);
        $quote->setBaseTransiteoTotalTaxes($this->totalTaxes / $this->taxexService->getCurrentCurrencyRate());
        $quote->save();
    }

    /**
     * Retrieve Transiteo Taxes
     *
     * @param Quote $quote
     * @param Total $total
     * @param ShippingAssignmentInterface|null $shippingAssignment
     */
    protected function getTransiteoTaxes($quote, $total, $shippingAssignment = null)
    {
        /**
         * @var \Magento\Quote\Api\Data\CartItemInterface $quoteItem
         */
        $products = [];
        if ($shippingAssignment) {
            $items = $shippingAssignment->getItems();
            if (count($items)>0) {
                //we are on checkout
                if ($quote->getIsCheckoutCart()) {
                    //////////////////LOGGER//////////////
                    $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/surcharge.log');
                    $logger = new \Zend\Log\Logger();
                    $logger->addWriter($writer);
                    $logger->info('We are in checkout ');
                    ///////////////////////////////////////
                    $params[TaxesService::DISALLOW_GET_COUNTRY_FROM_COOKIE] = true;
                }
            } else {
                $items = $quote->getItemsCollection();
            }
        } else {
            $items = $quote->getItemsCollection();
        }
//        $items = $quote->getItemsCollection();

        foreach ($items as $quoteItem) {
//            if ($shippingAssignment !== null || $quoteItem->getHasChildren()) {
            if ($quoteItem->getParentItem()) {
                continue;
            }

            $product = $quoteItem->getProduct();
            $id = $product->getId();
            $amount = $quoteItem->getQty();
            $products[$id] = ['qty' => $amount, 'product' => $product];
        }

        $params = [];
        //getShippingAmount
        $shippingAmount = $total->getShippingAmount();
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
            $districtId = $shippingAssignment->getShipping()->getAddress()->getRegionCode();
            if (!($countryId === "US" && empty($districtId)) && !empty($countryId)) {
                $params[TaxesService::TO_COUNTRY] = $countryId;
                $params[TaxesService::TO_DISTRICT] = $districtId;
            }
            //////////////////LOGGER//////////////
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/surcharge.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('Get Transiteo Taxes : { country_id : ' . $countryId . ' , region_id : ' . $districtId . ' , shipping_amount = ' . $shippingAmount . '} ');
            ///////////////////////////////////////
        }

        $taxes=[];
        if ($products !== []) {
            //get duties and taxes from taxes service
            $taxes= $this->taxexService->getDutiesByProducts($products, $params);
        } else {
            throw new \Exception('Product Cart is Empty from Transiteo Api.');
        }

        if (
            !array_key_exists(TaxesService::RETURN_KEY_DUTY, $taxes) ||
            !array_key_exists(TaxesService::RETURN_KEY_VAT, $taxes) ||
            !array_key_exists(TaxesService::RETURN_KEY_SPECIAL_TAXES, $taxes) ||
            !array_key_exists(TaxesService::RETURN_KEY_TOTAL_TAXES, $taxes)
        ) {
            throw new \Exception('Unable to get Duty and Taxes from Transiteo Api.');
        }

        //get duties
        $this->duty = $taxes[TaxesService::RETURN_KEY_DUTY];
        $this->vat =$taxes[TaxesService::RETURN_KEY_VAT];
        $this->specialTaxes = $taxes[TaxesService::RETURN_KEY_SPECIAL_TAXES];
        $this->totalTaxes = $taxes[TaxesService::RETURN_KEY_TOTAL_TAXES];
        //////////////////LOGGER//////////////
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/taxes.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('Duty : ' . $this->duty . ' Vat : ' . $this->vat . ' Special Taxes : ' . $this->specialTaxes . ' Total Taxes : ' . $this->totalTaxes);
        ///////////////////////////////////////
    }
}
