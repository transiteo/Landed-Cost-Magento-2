<?php

/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\LandedCost\Model\Order\Invoice;

use Transiteo\LandedCost\Api\Data\TransiteoItemTaxesExtensionInterface;
use Transiteo\LandedCost\Api\Data\TransiteoTaxesExtensionInterface;

class Surcharge extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{

    /**
     * Collect invoice subtotal
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();
        /**
         * @var TransiteoTaxesExtensionInterface $order
         */
        $baseTransiteoTotalTaxes = $order->getBaseTransiteoTotalTaxes();
        $transiteoTotalTaxes = $order->getTransiteoTotalTaxes();
        $baseTransiteoDuty = $order->getBaseTransiteoDuty();
        $transiteoDuty = $order->getTransiteoDuty() ;
        $baseTransiteoVat = $order->getBaseTransiteoVat() ;
        $transiteoVat = $order->getTransiteoVat();
        $baseTransiteoSpecialTaxes = $order->getBaseTransiteoSpecialTaxes();
        $transiteoSpecialTaxes = $order->getTransiteoSpecialTaxes();

        if(isset($transiteoTotalTaxes)){
            foreach ($invoice->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                $orderItemQty = $orderItem->getQtyOrdered();

                if (!$orderItemQty || $orderItem->isDummy() || $item->getQty() < 0) {
                    continue;
                }

                $ratio = ($orderItemQty - $item->getQty()) / $orderItemQty ;

                /**
                 * @var TransiteoItemTaxesExtensionInterface $orderItem
                 * @var TransiteoItemTaxesExtensionInterface $item
                 */
                if(isset($baseTransiteoTotalTaxes)){
                    $initialAmount = ($orderItem->getBaseTransiteoTotalTaxes() ?? 0.0);
                    $amountToSubtract = $initialAmount * $ratio;
                    $item->setBaseTransiteoTotalTaxes($invoice->roundPrice($initialAmount - $amountToSubtract, 'base'));
                    $baseTransiteoTotalTaxes -= $amountToSubtract;
                }
                $initialAmount = ($orderItem->getTransiteoTotalTaxes() ?? 0.0);
                $amountToSubtract = $initialAmount * $ratio;
                $item->setTransiteoTotalTaxes($invoice->roundPrice($initialAmount - $amountToSubtract, ''));
                $transiteoTotalTaxes -= $amountToSubtract;
                if(isset($baseTransiteoDuty)){
                    $initialAmount = ($orderItem->getBaseTransiteoDuty() ?? 0.0);
                    $amountToSubtract = $initialAmount * $ratio;
                    $item->setBaseTransiteoDuty($invoice->roundPrice($initialAmount - $amountToSubtract, 'base'));
                    $baseTransiteoDuty -= $amountToSubtract;
                }
                if(isset($transiteoDuty)){
                    $initialAmount = ($orderItem->getTransiteoDuty() ?? 0.0);
                    $amountToSubtract = $initialAmount * $ratio;
                    $item->setTransiteoDuty($invoice->roundPrice($initialAmount - $amountToSubtract, ''));
                    $transiteoDuty -= $amountToSubtract;
                }
                if(isset($baseTransiteoVat)){
                    $initialAmount = ($orderItem->getBaseTransiteoVat() ?? 0.0);
                    $amountToSubtract = $initialAmount * $ratio;
                    $item->setBaseTransiteoVat($invoice->roundPrice($initialAmount - $amountToSubtract, 'base'));
                    $baseTransiteoVat -= $amountToSubtract;
                }
                if(isset($transiteoVat)){
                    $initialAmount = ($orderItem->getTransiteoVat() ?? 0.0);
                    $amountToSubtract = $initialAmount * $ratio;
                    $item->setTransiteoVat($invoice->roundPrice($initialAmount - $amountToSubtract, ''));
                    $transiteoVat -= $amountToSubtract;
                }
                if(isset($baseTransiteoSpecialTaxes)){
                    $initialAmount = ($orderItem->getBaseTransiteoSpecialTaxes() ?? 0.0);
                    $amountToSubtract = $initialAmount * $ratio;
                    $item->setBaseTransiteoSpecialTaxes($invoice->roundPrice($initialAmount - $amountToSubtract, 'base'));
                    $baseTransiteoSpecialTaxes -= $amountToSubtract;
                }
                if(isset($transiteoSpecialTaxes)){
                    $initialAmount = ($orderItem->getTransiteoSpecialTaxes() ?? 0.0);
                    $amountToSubtract = $initialAmount * $ratio;
                    $item->setTransiteoSpecialTaxes($invoice->roundPrice($initialAmount - $amountToSubtract, ''));
                    $transiteoSpecialTaxes -= $amountToSubtract;
                }
            }
            /**
             *  @var TransiteoTaxesExtensionInterface $invoice
             */
            $invoice->setTransiteoIncoterm($order->getTransiteoIncoterm());
            $invoice->setBaseTransiteoTotalTaxes($baseTransiteoTotalTaxes);
            $invoice->setTransiteoTotalTaxes($transiteoTotalTaxes);
            $invoice->setBaseTransiteoSpecialTaxes($baseTransiteoSpecialTaxes);
            $invoice->setTransiteoSpecialTaxes($transiteoSpecialTaxes);
            $invoice->setBaseTransiteoDuty($baseTransiteoDuty);
            $invoice->setTransiteoDuty($transiteoDuty);
            $invoice->setBaseTransiteoVat($baseTransiteoVat);
            $invoice->setTransiteoVat($transiteoVat);

            if($invoice->getTransiteoIncoterm() === "ddp"){
                $invoice->setGrandTotal($invoice->getGrandTotal() + $transiteoTotalTaxes);
                $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseTransiteoTotalTaxes);
            }
        }
        return $this;
    }
}
