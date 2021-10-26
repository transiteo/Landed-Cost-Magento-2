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
