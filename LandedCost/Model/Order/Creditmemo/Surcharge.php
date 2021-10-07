<?php

/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\LandedCost\Model\Order\Creditmemo;

use Transiteo\LandedCost\Api\Data\TransiteoItemTaxesExtensionInterface;
use Transiteo\LandedCost\Api\Data\TransiteoTaxesExtensionInterface;

class Surcharge extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this|Surcharge
     */
    function collect( \Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
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
            foreach ($creditmemo->getAllItems() as $item) {
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
                    $item->setBaseTransiteoTotalTaxes($creditmemo->roundPrice($initialAmount - $amountToSubtract, 'base'));
                    $baseTransiteoTotalTaxes -= $amountToSubtract;
                }
                $initialAmount = ($orderItem->getTransiteoTotalTaxes() ?? 0.0);
                $amountToSubtract = $initialAmount * $ratio;
                $item->setTransiteoTotalTaxes($creditmemo->roundPrice($initialAmount - $amountToSubtract, ''));
                $transiteoTotalTaxes -= $amountToSubtract;
                if(isset($baseTransiteoDuty)){
                    $initialAmount = ($orderItem->getBaseTransiteoDuty() ?? 0.0);
                    $amountToSubtract = $initialAmount * $ratio;
                    $item->setBaseTransiteoDuty($creditmemo->roundPrice($initialAmount - $amountToSubtract, 'base'));
                    $baseTransiteoDuty -= $amountToSubtract;
                }
                if(isset($transiteoDuty)){
                    $initialAmount = ($orderItem->getTransiteoDuty() ?? 0.0);
                    $amountToSubtract = $initialAmount * $ratio;
                    $item->setTransiteoDuty($creditmemo->roundPrice($initialAmount - $amountToSubtract, ''));
                    $transiteoDuty -= $amountToSubtract;
                }
                if(isset($baseTransiteoVat)){
                    $initialAmount = ($orderItem->getBaseTransiteoVat() ?? 0.0);
                    $amountToSubtract = $initialAmount * $ratio;
                    $item->setBaseTransiteoVat($creditmemo->roundPrice($initialAmount - $amountToSubtract, 'base'));
                    $baseTransiteoVat -= $amountToSubtract;
                }
                if(isset($transiteoVat)){
                    $initialAmount = ($orderItem->getTransiteoVat() ?? 0.0);
                    $amountToSubtract = $initialAmount * $ratio;
                    $item->setTransiteoVat($creditmemo->roundPrice($initialAmount - $amountToSubtract, ''));
                    $transiteoVat -= $amountToSubtract;
                }
                if(isset($baseTransiteoSpecialTaxes)){
                    $initialAmount = ($orderItem->getBaseTransiteoSpecialTaxes() ?? 0.0);
                    $amountToSubtract = $initialAmount * $ratio;
                    $item->setBaseTransiteoSpecialTaxes($creditmemo->roundPrice($initialAmount - $amountToSubtract, 'base'));
                    $baseTransiteoSpecialTaxes -= $amountToSubtract;
                }
                if(isset($transiteoSpecialTaxes)){
                    $initialAmount = ($orderItem->getTransiteoSpecialTaxes() ?? 0.0);
                    $amountToSubtract = $initialAmount * $ratio;
                    $item->setTransiteoSpecialTaxes($creditmemo->roundPrice($initialAmount - $amountToSubtract, ''));
                    $transiteoSpecialTaxes -= $amountToSubtract;
                }
            }

            /**
             *  @var TransiteoTaxesExtensionInterface $creditmemo
             */
            $creditmemo->setTransiteoIncoterm($order->getTransiteoIncoterm());
            $creditmemo->setBaseTransiteoTotalTaxes($baseTransiteoTotalTaxes);
            $creditmemo->setTransiteoTotalTaxes($transiteoTotalTaxes);
            $creditmemo->setBaseTransiteoSpecialTaxes($baseTransiteoSpecialTaxes);
            $creditmemo->setTransiteoSpecialTaxes($transiteoSpecialTaxes);
            $creditmemo->setBaseTransiteoDuty($baseTransiteoDuty);
            $creditmemo->setTransiteoDuty($transiteoDuty);
            $creditmemo->setBaseTransiteoVat($baseTransiteoVat);
            $creditmemo->setTransiteoVat($transiteoVat);

            if($creditmemo->getTransiteoIncoterm() === "ddp"){
                $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $transiteoTotalTaxes);
                $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseTransiteoTotalTaxes);
            }
        }
        return $this;
    }
}
