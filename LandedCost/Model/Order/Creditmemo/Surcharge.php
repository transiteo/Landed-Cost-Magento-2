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
                 */
                if(isset($baseTransiteoTotalTaxes)){
                    $baseTransiteoTotalTaxes -= $creditmemo->roundPrice(($orderItem->getBaseTransiteoTotalTaxes() ?? 0.0) * $ratio, 'base');
                }
                $transiteoTotalTaxes -= $creditmemo->roundPrice(($orderItem->getTransiteoTotalTaxes() ?? 0.0) * $ratio);
                if(isset($baseTransiteoDuty)){
                    $baseTransiteoDuty -= $creditmemo->roundPrice(($orderItem->getBaseTransiteoDuty() ?? 0.0) * $ratio, 'base');
                }
                if(isset($transiteoDuty)){
                    $transiteoDuty -= $creditmemo->roundPrice(($orderItem->getTransiteoDuty() ?? 0.0) * $ratio);
                }
                if(isset($baseTransiteoVat)){
                    $baseTransiteoVat -= $creditmemo->roundPrice(($orderItem->getBaseTransiteoVat() ?? 0.0) * $ratio, 'base');
                }
                if(isset($transiteoVat)){
                    $transiteoVat -= $creditmemo->roundPrice(($orderItem->getTransiteoVat() ?? 0.0) * $ratio);
                }
                if(isset($baseTransiteoSpecialTaxes)){
                    $baseTransiteoSpecialTaxes -= $creditmemo->roundPrice(($orderItem->getBaseTransiteoSpecialTaxes() ?? 0.0) * $ratio);
                }
                if(isset($transiteoSpecialTaxes)){
                    $transiteoSpecialTaxes -= $creditmemo->roundPrice(($orderItem->getTransiteoSpecialTaxes() ?? 0.0) * $ratio);
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

            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $transiteoTotalTaxes);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseTransiteoTotalTaxes);
        }
        return $this;
    }
}
