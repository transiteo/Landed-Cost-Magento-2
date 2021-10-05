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
                 */
                if(isset($baseTransiteoTotalTaxes)){
                    $baseTransiteoTotalTaxes -= $invoice->roundPrice(($orderItem->getBaseTransiteoTotalTaxes() ?? 0.0) * $ratio, 'base');
                }
                $transiteoTotalTaxes -= $invoice->roundPrice(($orderItem->getTransiteoTotalTaxes() ?? 0.0) * $ratio);
                if(isset($baseTransiteoDuty)){
                    $baseTransiteoDuty -= $invoice->roundPrice(($orderItem->getBaseTransiteoDuty() ?? 0.0) * $ratio, 'base');
                }
                if(isset($transiteoDuty)){
                    $transiteoDuty -= $invoice->roundPrice(($orderItem->getTransiteoDuty() ?? 0.0) * $ratio);
                }
                if(isset($baseTransiteoVat)){
                    $baseTransiteoVat -= $invoice->roundPrice(($orderItem->getBaseTransiteoVat() ?? 0.0) * $ratio, 'base');
                }
                if(isset($transiteoVat)){
                    $transiteoVat -= $invoice->roundPrice(($orderItem->getTransiteoVat() ?? 0.0) * $ratio);
                }
                if(isset($baseTransiteoSpecialTaxes)){
                    $baseTransiteoSpecialTaxes -= $invoice->roundPrice(($orderItem->getBaseTransiteoSpecialTaxes() ?? 0.0)* $ratio);
                }
                if(isset($transiteoSpecialTaxes)){
                    $transiteoSpecialTaxes -= $invoice->roundPrice(($orderItem->getTransiteoSpecialTaxes() ?? 0.0) * $ratio);
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

            $invoice->setGrandTotal($invoice->getGrandTotal() + $transiteoTotalTaxes);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseTransiteoTotalTaxes);
        }
        return $this;
    }
}
