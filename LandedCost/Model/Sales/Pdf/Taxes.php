<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Transiteo\LandedCost\Model\Sales\Pdf;

use Transiteo\LandedCost\Api\Data\TransiteoTaxesExtensionInterface;

/**
 * Sales order total for PDF, taking into account WEEE tax
 */
class Taxes extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{

    /**
     * Check if weee total amount should be included
     *
     * Example:
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     *
     * @return array
     */
    public function getTotalsForDisplay()
    {
        /**
         * @var TransiteoTaxesExtensionInterface $order
         */
        $order = $this->getOrder();


        $totals = [];
        if ($order) {
            $transiteoTotalTaxes = $order->getTransiteoTotalTaxes();
            if($transiteoTotalTaxes){
                $baseTransiteoTotalTaxes = $order->getBaseTransiteoTotalTaxes();
                $transiteoDuty = $order->getTransiteoDuty();
                $baseTransiteoDuty = $order->getBaseTransiteoDuty();
                $transiteoVat = $order->getTransiteoVat();
                $baseTransiteoVat = $order->getBaseTransiteoVat();
                $transiteoSpecialTaxes = $order->getTransiteoSpecialTaxes();
                $baseTransiteoSpecialTaxes = $order->getBaseTransiteoSpecialTaxes();
                $included = $order->getTransiteoIncoterm();
                if ($included === "ddp") {
                    $included = ' ' . __('(included)');
                } else {
                    $included = ' ' . __('(not included)');
                }

                $totalSetted = 0;
                if (isset($transiteoVat)) {
                    $totalSetted++;
                }
                if (isset($transiteoDuty)) {
                    $totalSetted++;
                }
                if (isset($transiteoSpecialTaxes)) {
                    $totalSetted++;
                }

                if (($totalSetted > 1)) {
                    $totals['transiteo_total_taxes'] = new \Magento\Framework\DataObject(
                        [
                            'code' => 'transiteo_total_taxes',
                            'field' => 'transiteo_total_taxes_amount',
                            'strong' => true,
                            'value' => $transiteoTotalTaxes,
                            'base_value' => $baseTransiteoTotalTaxes,
                            'label' => __('Duty & Taxes Total' . $included),
                        ]
                    );
                }

                if (isset($transiteoSpecialTaxes)) {
                    $totals['transiteo_special_taxes'] = new \Magento\Framework\DataObject(
                        [
                            'code' => 'transiteo_special_taxes',
                            'field' => 'transiteo_special_taxes_amount',
                            'value' => $transiteoSpecialTaxes,
                            'base_value' => $baseTransiteoSpecialTaxes,
                            'label' => __('Special Taxes SubTotal' . $included),
                        ]
                    );
                }

                if (isset($transiteoVat)) {
                    $totals['transiteo_vat'] = new \Magento\Framework\DataObject(
                        [
                            'code' => 'transiteo_vat',
                            'field' => 'transiteo_vat_amount',
                            'value' => $transiteoVat,
                            'base_value' => $baseTransiteoVat,
                            'label' => __('VAT/GST SubTotal' . $included),
                        ]
                    );
                }

                if (isset($transiteoDuty)) {
                    $totals['transiteo_duty'] = new \Magento\Framework\DataObject(
                        [
                            'code' => 'transiteo_duty',
                            'field' => 'transiteo_duty_amount',
                            'value' => $transiteoDuty,
                            'base_value' => $baseTransiteoDuty,
                            'label' => __('Duty SubTotal' . $included),
                        ]
                    );
                }
            }
        }
        return $totals;
    }

    /**
     * Check if we can display Weee total information in PDF
     *
     * @return bool
     */
    public function canDisplay()
    {
        /**
         * @var TransiteoTaxesExtensionInterface $orderAttributes
         */
        $amount = $this->getOrder()->getTransiteoTotalTaxes();
        return $this->getDisplayZero() === 'true' || $amount != 0;
    }
}
