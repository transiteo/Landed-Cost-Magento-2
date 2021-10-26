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

namespace Transiteo\LandedCost\Model\Order\Creditmemo;

use Transiteo\LandedCost\Api\Data\TransiteoItemTaxesExtensionInterface;
use Transiteo\LandedCost\Api\Data\TransiteoTaxesExtensionInterface;

class Surcharge extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    /**
     * @var \Transiteo\LandedCost\Model\Config
     */
    protected $config;

    public function __construct(
        \Transiteo\LandedCost\Model\Config $config,
        array $data = []
    )
    {
        $this->config = $config;
        parent::__construct($data);
    }

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
        $baseTransiteoTotalTaxes = (float) $order->getBaseTransiteoTotalTaxes();
        $transiteoTotalTaxes = (float) $order->getTransiteoTotalTaxes();
        $baseTransiteoDuty = (float) $order->getBaseTransiteoDuty();
        $transiteoDuty = (float) $order->getTransiteoDuty() ;
        $baseTransiteoVat = (float) $order->getBaseTransiteoVat() ;
        $transiteoVat = (float) $order->getTransiteoVat();
        $baseTransiteoSpecialTaxes = (float) $order->getBaseTransiteoSpecialTaxes();
        $transiteoSpecialTaxes = (float) $order->getTransiteoSpecialTaxes();

        /**
         * Calculate final taxes (applied to the Shipments, not to the products)
         */
        $finalBaseTransiteoTotalTaxes = $baseTransiteoTotalTaxes;
        $finalTransiteoTotalTaxes = $transiteoTotalTaxes;
        $finalBaseTransiteoDuty = $baseTransiteoDuty;
        $finalTransiteoDuty = $transiteoDuty ;
        $finalBaseTransiteoVat = $baseTransiteoVat ;
        $finalTransiteoVat = $transiteoVat;
        $finalBaseTransiteoSpecialTaxes = $baseTransiteoSpecialTaxes;
        $finalTransiteoSpecialTaxes = $transiteoSpecialTaxes;

        /**
         * Used to calculate the already creditmemod amounts
         */
        $amountAvailableBaseTransiteoTotalTaxes = $baseTransiteoTotalTaxes;
        $amountAvailableTransiteoTotalTaxes = $transiteoTotalTaxes;
        $amountAvailableBaseTransiteoDuty = $baseTransiteoDuty;
        $amountAvailableTransiteoDuty = $transiteoDuty ;
        $amountAvailableBaseTransiteoVat = $baseTransiteoVat ;
        $amountAvailableTransiteoVat = $transiteoVat;
        $amountAvailableBaseTransiteoSpecialTaxes = $baseTransiteoSpecialTaxes;
        $amountAvailableTransiteoSpecialTaxes = $transiteoSpecialTaxes;


        $creditmemoQty = 0;
        $items = $creditmemo->getAllItems();
        if(isset($transiteoTotalTaxes)){
            if(!$this->config->getIsPriceIncludingTaxes()){
                foreach ($items as $item) {
                    $orderItem = $item->getOrderItem();
                    $orderItemQty = $orderItem->getQtyOrdered();
                    if (!$orderItemQty || $orderItem->isDummy() || $item->getQty() < 0) {
                        continue;
                    }

                    $creditmemoItemQty = $item->getQty();
                    $creditmemoQty += $creditmemoItemQty;

                    $ratio = ($orderItemQty - $creditmemoItemQty) / $orderItemQty ;

                    /**
                     * @var TransiteoItemTaxesExtensionInterface $orderItem
                     * @var TransiteoItemTaxesExtensionInterface $item
                     */
                    if(isset($baseTransiteoTotalTaxes)){
                        $initialAmount = ($orderItem->getBaseTransiteoTotalTaxes() ?? 0.0);
                        $finalBaseTransiteoTotalTaxes -= $initialAmount;
                        $amountToSubtract = $initialAmount * $ratio;
                        $item->setBaseTransiteoTotalTaxes($creditmemo->roundPrice($initialAmount - $amountToSubtract, 'base'));
                        $baseTransiteoTotalTaxes -= $amountToSubtract;
                    }
                    $initialAmount = ($orderItem->getTransiteoTotalTaxes() ?? 0.0);
                    $finalTransiteoTotalTaxes -= $initialAmount;
                    $amountToSubtract = $initialAmount * $ratio;
                    $item->setTransiteoTotalTaxes($creditmemo->roundPrice($initialAmount - $amountToSubtract, ''));
                    $transiteoTotalTaxes -= $amountToSubtract;
                    if(isset($baseTransiteoDuty)){
                        $initialAmount = ($orderItem->getBaseTransiteoDuty() ?? 0.0);
                        $finalBaseTransiteoDuty -= $initialAmount;
                        $amountToSubtract = $initialAmount * $ratio;
                        $item->setBaseTransiteoDuty($creditmemo->roundPrice($initialAmount - $amountToSubtract, 'base'));
                        $baseTransiteoDuty -= $amountToSubtract;
                    }
                    if(isset($transiteoDuty)){
                        $initialAmount = ($orderItem->getTransiteoDuty() ?? 0.0);
                        $finalTransiteoDuty -= $initialAmount;
                        $amountToSubtract = $initialAmount * $ratio;
                        $item->setTransiteoDuty($creditmemo->roundPrice($initialAmount - $amountToSubtract, ''));
                        $transiteoDuty -= $amountToSubtract;
                    }
                    if(isset($baseTransiteoVat)){
                        $initialAmount = ($orderItem->getBaseTransiteoVat() ?? 0.0);
                        $finalBaseTransiteoVat -= $initialAmount;
                        $amountToSubtract = $initialAmount * $ratio;
                        $item->setBaseTransiteoVat($creditmemo->roundPrice($initialAmount - $amountToSubtract, 'base'));
                        $baseTransiteoVat -= $amountToSubtract;
                    }
                    if(isset($transiteoVat)){
                        $initialAmount = ($orderItem->getTransiteoVat() ?? 0.0);
                        $finalTransiteoVat -= $initialAmount;
                        $amountToSubtract = $initialAmount * $ratio;
                        $item->setTransiteoVat($creditmemo->roundPrice($initialAmount - $amountToSubtract, ''));
                        $transiteoVat -= $amountToSubtract;
                    }
                    if(isset($baseTransiteoSpecialTaxes)){
                        $initialAmount = ($orderItem->getBaseTransiteoSpecialTaxes() ?? 0.0);
                        $finalBaseTransiteoSpecialTaxes -= $initialAmount;
                        $amountToSubtract = $initialAmount * $ratio;
                        $item->setBaseTransiteoSpecialTaxes($creditmemo->roundPrice($initialAmount - $amountToSubtract, 'base'));
                        $baseTransiteoSpecialTaxes -= $amountToSubtract;
                    }
                    if(isset($transiteoSpecialTaxes)){
                        $initialAmount = ($orderItem->getTransiteoSpecialTaxes() ?? 0.0);
                        $finalTransiteoSpecialTaxes -= $initialAmount;
                        $amountToSubtract = $initialAmount * $ratio;
                        $item->setTransiteoSpecialTaxes($creditmemo->roundPrice($initialAmount - $amountToSubtract, ''));
                        $transiteoSpecialTaxes -= $amountToSubtract;
                    }
                }
            }else{
                //Get the creditmemod qty
                foreach ($items as $item) {
                    $orderItem = $item->getOrderItem();
                    $orderItemQty = $orderItem->getQtyOrdered();
                    if (!$orderItemQty || $orderItem->isDummy() || $item->getQty() < 0) {
                        continue;
                    }
                    $creditmemoQty += $item->getQty();
                }
            }

            /**
             * Calculate final taxes (applied to the Shipments, not to the products) divided by the qty of products
             */
            $qtyOrdered =  $order->getTotalQtyOrdered();
            $ratio = ($qtyOrdered - $creditmemoQty) / $qtyOrdered;
            if(isset($baseTransiteoTotalTaxes)){
                $baseTransiteoTotalTaxes -= $finalBaseTransiteoTotalTaxes * $ratio;
            }
            $transiteoTotalTaxes -= $finalTransiteoTotalTaxes * $ratio;
            if(isset($baseTransiteoDuty)){
                $baseTransiteoDuty -= $finalBaseTransiteoDuty * $ratio;
            }
            if(isset($transiteoDuty)){
                $transiteoDuty -= $finalTransiteoDuty * $ratio;
            }
            if(isset($baseTransiteoVat)){
                $baseTransiteoVat -= $finalBaseTransiteoVat * $ratio;
            }
            if(isset($transiteoVat)){
                $transiteoVat -= $finalTransiteoVat * $ratio;
            }
            if(isset($baseTransiteoSpecialTaxes)){
                $baseTransiteoSpecialTaxes -= $finalBaseTransiteoSpecialTaxes * $ratio;
            }
            if(isset($transiteoSpecialTaxes)){
                $transiteoSpecialTaxes -= $finalTransiteoSpecialTaxes * $ratio;
            }

            /**
             * Check other creditmemos, to avoid round issues and not to creditmemo more than the taxes
             */
            if($order->hasCreditmemos()){
                foreach ($order->getCreditmemosCollection()->getItems() as $c){
                    /**
                     *  @var TransiteoTaxesExtensionInterface $c
                     */
                    $amountAvailableBaseTransiteoTotalTaxes -= $c->getBaseTransiteoTotalTaxes();
                    $amountAvailableTransiteoTotalTaxes -= $c->getTransiteoTotalTaxes();
                    $amountAvailableBaseTransiteoDuty -= $c->getBaseTransiteoDuty();
                    $amountAvailableTransiteoDuty -= $c->getTransiteoDuty() ;
                    $amountAvailableBaseTransiteoVat -= $c->getBaseTransiteoVat() ;
                    $amountAvailableTransiteoVat -= $c->getTransiteoVat();
                    $amountAvailableBaseTransiteoSpecialTaxes -= $c->getBaseTransiteoSpecialTaxes();
                    $amountAvailableTransiteoSpecialTaxes -= $c->getTransiteoSpecialTaxes();
                }
            }

            if(isset($baseTransiteoTotalTaxes)){
                $amountAvailableToDispatch = $amountAvailableBaseTransiteoTotalTaxes;
                if($baseTransiteoTotalTaxes < 0){
                    if($baseTransiteoTotalTaxes < $amountAvailableToDispatch){
                        $baseTransiteoTotalTaxes = $amountAvailableToDispatch;
                    }
                }elseif($baseTransiteoTotalTaxes > $amountAvailableToDispatch){
                    $baseTransiteoTotalTaxes = $amountAvailableToDispatch;
                }
            }

            $amountAvailableToDispatch = $amountAvailableTransiteoTotalTaxes;
            if($transiteoTotalTaxes < 0){
                if($transiteoTotalTaxes < $amountAvailableToDispatch){
                    $transiteoTotalTaxes = $amountAvailableToDispatch;
                }
            }elseif($transiteoTotalTaxes > $amountAvailableToDispatch){
                $transiteoTotalTaxes = $amountAvailableToDispatch;
            }

            if(isset($baseTransiteoDuty)){
                $amountAvailableToDispatch = $amountAvailableBaseTransiteoDuty;
                if($baseTransiteoDuty < 0){
                    if($baseTransiteoDuty < $amountAvailableToDispatch){
                        $baseTransiteoDuty = $amountAvailableToDispatch;
                    }
                }elseif($baseTransiteoDuty > $amountAvailableToDispatch){
                    $baseTransiteoDuty = $amountAvailableToDispatch;
                }
            }

            if(isset($transiteoDuty)){
                $amountAvailableToDispatch = $amountAvailableTransiteoDuty;
                if($transiteoDuty < 0){
                    if($transiteoDuty < $amountAvailableToDispatch){
                        $transiteoDuty = $amountAvailableToDispatch;
                    }
                }elseif($transiteoDuty > $amountAvailableToDispatch){
                    $transiteoDuty = $amountAvailableToDispatch;
                }
            }

            if(isset($baseTransiteoVat)){
                $amountAvailableToDispatch = $amountAvailableBaseTransiteoVat;
                if($baseTransiteoVat < 0){
                    if($baseTransiteoVat < $amountAvailableToDispatch){
                        $baseTransiteoVat = $amountAvailableToDispatch;
                    }
                }elseif($baseTransiteoVat > $amountAvailableToDispatch){
                    $baseTransiteoVat = $amountAvailableToDispatch;
                }
            }

            if(isset($transiteoVat)){
                $amountAvailableToDispatch =  $amountAvailableTransiteoVat;
                if($transiteoVat < 0){
                    if($transiteoVat < $amountAvailableToDispatch){
                        $transiteoVat = $amountAvailableToDispatch;
                    }
                }elseif($transiteoVat > $amountAvailableToDispatch){
                    $transiteoVat = $amountAvailableToDispatch;
                }
            }

            if(isset($baseTransiteoSpecialTaxes)){
                $amountAvailableToDispatch = $amountAvailableBaseTransiteoSpecialTaxes;
                if($baseTransiteoSpecialTaxes < 0){
                    if($baseTransiteoSpecialTaxes < $amountAvailableToDispatch){
                        $baseTransiteoSpecialTaxes = $amountAvailableToDispatch;
                    }
                }elseif($baseTransiteoSpecialTaxes > $amountAvailableToDispatch){
                    $baseTransiteoSpecialTaxes = $amountAvailableToDispatch;
                }
            }

            if(isset($transiteoSpecialTaxes)){
                $amountAvailableToDispatch = $amountAvailableTransiteoSpecialTaxes;
                if($transiteoSpecialTaxes < 0){
                    if($transiteoSpecialTaxes < $amountAvailableToDispatch){
                        $transiteoSpecialTaxes = $amountAvailableToDispatch;
                    }
                }elseif($transiteoSpecialTaxes > $amountAvailableToDispatch){
                    $transiteoSpecialTaxes = $amountAvailableToDispatch;
                }
            }


            /**
             * Round prices
             */
            if(isset($baseTransiteoTotalTaxes)){
                $baseTransiteoTotalTaxes = $creditmemo->roundPrice($transiteoTotalTaxes, 'base', $baseTransiteoTotalTaxes < 0);
            }
            $transiteoTotalTaxes = $creditmemo->roundPrice($transiteoTotalTaxes, 'regular', $transiteoTotalTaxes < 0);

            if(isset($baseTransiteoDuty)){
                $baseTransiteoDuty = $creditmemo->roundPrice($transiteoDuty, 'base', $baseTransiteoDuty < 0);
            }
            if(isset($transiteoDuty)){
                $transiteoDuty = $creditmemo->roundPrice($transiteoDuty, 'regular', $transiteoDuty < 0);
            }
            if(isset($baseTransiteoVat)){
                $baseTransiteoVat = $creditmemo->roundPrice($transiteoVat, 'base', $baseTransiteoVat < 0);
            }
            if(isset($transiteoVat)){
                $transiteoVat = $creditmemo->roundPrice($transiteoVat, 'regular', $transiteoVat < 0);
            }
            if(isset($baseTransiteoSpecialTaxes)){
                $baseTransiteoSpecialTaxes = $creditmemo->roundPrice($transiteoSpecialTaxes, 'base', $baseTransiteoSpecialTaxes < 0);
            }
            if(isset($transiteoSpecialTaxes)){
                $transiteoSpecialTaxes = $creditmemo->roundPrice($transiteoSpecialTaxes, 'regular', $transiteoSpecialTaxes < 0);
            }

            /**
             * Apply the taxes to the creditmemo
             */



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
