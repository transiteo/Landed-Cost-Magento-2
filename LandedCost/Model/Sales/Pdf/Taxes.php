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
         * @var TransiteoTaxesExtensionInterface $salesEntity
         */
        $salesEntity = $this->getSource();

        $isCreditMemo = false;
        $isInvoice = false;
        if($salesEntity->getEntityType() === "creditmemo"){
            $isCreditMemo = true;
        }elseif ($salesEntity->getEntityType() === "invoice"){
            $isInvoice = true;
        }

        $incoterm = $salesEntity->getTransiteoIncoterm();
        if (isset($incoterm) && (!$isCreditMemo || $incoterm==="ddp")) {
            if ($incoterm === "ddp") {
                $included = ' (' . __('included') . ')';
                if($isCreditMemo || $isInvoice){
                    $included = '';
                }
            } else {
                $included = ' (' . __('not included') . ')';
            }
        }

        $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
        if ($this->getAmountPrefix()) {
            $amount = $this->getAmountPrefix() . $amount;
        }

        $label = __($this->getTitle()) . $included;

        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $total = ['amount' => $amount, 'label' => $label, 'font_size' => $fontSize];
        return [$total];
    }
}
