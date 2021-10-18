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
         * Used to calculate the already invoiced amounts
         */
        $amountAvailableBaseTransiteoTotalTaxes = $baseTransiteoTotalTaxes;
        $amountAvailableTransiteoTotalTaxes = $transiteoTotalTaxes;
        $amountAvailableBaseTransiteoDuty = $baseTransiteoDuty;
        $amountAvailableTransiteoDuty = $transiteoDuty ;
        $amountAvailableBaseTransiteoVat = $baseTransiteoVat ;
        $amountAvailableTransiteoVat = $transiteoVat;
        $amountAvailableBaseTransiteoSpecialTaxes = $baseTransiteoSpecialTaxes;
        $amountAvailableTransiteoSpecialTaxes = $transiteoSpecialTaxes;


        $invoiceQty = 0;
        $items = $invoice->getAllItems();
        if(isset($transiteoTotalTaxes)){
            if(!$this->config->getIsPriceIncludingTaxes()){
                foreach ($items as $item) {
                    $orderItem = $item->getOrderItem();
                    $orderItemQty = $orderItem->getQtyOrdered();
                    if (!$orderItemQty || $orderItem->isDummy() || $item->getQty() < 0) {
                        continue;
                    }

                    $invoiceItemQty = $item->getQty();
                    $invoiceQty += $invoiceItemQty;

                    $ratio = ($orderItemQty - $invoiceItemQty) / $orderItemQty ;

                    /**
                     * @var TransiteoItemTaxesExtensionInterface $orderItem
                     * @var TransiteoItemTaxesExtensionInterface $item
                     */
                    if(isset($baseTransiteoTotalTaxes)){
                        $initialAmount = ($orderItem->getBaseTransiteoTotalTaxes() ?? 0.0);
                        $finalBaseTransiteoTotalTaxes -= $initialAmount;
                        $amountToSubtract = $initialAmount * $ratio;
                        $item->setBaseTransiteoTotalTaxes($invoice->roundPrice($initialAmount - $amountToSubtract, 'base'));
                        $baseTransiteoTotalTaxes -= $amountToSubtract;
                    }
                    $initialAmount = ($orderItem->getTransiteoTotalTaxes() ?? 0.0);
                    $finalTransiteoTotalTaxes -= $initialAmount;
                    $amountToSubtract = $initialAmount * $ratio;
                    $item->setTransiteoTotalTaxes($invoice->roundPrice($initialAmount - $amountToSubtract, ''));
                    $transiteoTotalTaxes -= $amountToSubtract;
                    if(isset($baseTransiteoDuty)){
                        $initialAmount = ($orderItem->getBaseTransiteoDuty() ?? 0.0);
                        $finalBaseTransiteoDuty -= $initialAmount;
                        $amountToSubtract = $initialAmount * $ratio;
                        $item->setBaseTransiteoDuty($invoice->roundPrice($initialAmount - $amountToSubtract, 'base'));
                        $baseTransiteoDuty -= $amountToSubtract;
                    }
                    if(isset($transiteoDuty)){
                        $initialAmount = ($orderItem->getTransiteoDuty() ?? 0.0);
                        $finalTransiteoDuty -= $initialAmount;
                        $amountToSubtract = $initialAmount * $ratio;
                        $item->setTransiteoDuty($invoice->roundPrice($initialAmount - $amountToSubtract, ''));
                        $transiteoDuty -= $amountToSubtract;
                    }
                    if(isset($baseTransiteoVat)){
                        $initialAmount = ($orderItem->getBaseTransiteoVat() ?? 0.0);
                        $finalBaseTransiteoVat -= $initialAmount;
                        $amountToSubtract = $initialAmount * $ratio;
                        $item->setBaseTransiteoVat($invoice->roundPrice($initialAmount - $amountToSubtract, 'base'));
                        $baseTransiteoVat -= $amountToSubtract;
                    }
                    if(isset($transiteoVat)){
                        $initialAmount = ($orderItem->getTransiteoVat() ?? 0.0);
                        $finalTransiteoVat -= $initialAmount;
                        $amountToSubtract = $initialAmount * $ratio;
                        $item->setTransiteoVat($invoice->roundPrice($initialAmount - $amountToSubtract, ''));
                        $transiteoVat -= $amountToSubtract;
                    }
                    if(isset($baseTransiteoSpecialTaxes)){
                        $initialAmount = ($orderItem->getBaseTransiteoSpecialTaxes() ?? 0.0);
                        $finalBaseTransiteoSpecialTaxes -= $initialAmount;
                        $amountToSubtract = $initialAmount * $ratio;
                        $item->setBaseTransiteoSpecialTaxes($invoice->roundPrice($initialAmount - $amountToSubtract, 'base'));
                        $baseTransiteoSpecialTaxes -= $amountToSubtract;
                    }
                    if(isset($transiteoSpecialTaxes)){
                        $initialAmount = ($orderItem->getTransiteoSpecialTaxes() ?? 0.0);
                        $finalTransiteoSpecialTaxes -= $initialAmount;
                        $amountToSubtract = $initialAmount * $ratio;
                        $item->setTransiteoSpecialTaxes($invoice->roundPrice($initialAmount - $amountToSubtract, ''));
                        $transiteoSpecialTaxes -= $amountToSubtract;
                    }
                }
            }else{
                //Get the invoiced qty
                foreach ($items as $item) {
                    $orderItem = $item->getOrderItem();
                    $orderItemQty = $orderItem->getQtyOrdered();
                    if (!$orderItemQty || $orderItem->isDummy() || $item->getQty() < 0) {
                        continue;
                    }
                    $invoiceQty += $item->getQty();
                }
            }

            /**
             * Calculate final taxes (applied to the Shipments, not to the products) divided by the qty of products
             */
            $qtyOrdered =  $order->getTotalQtyOrdered();
            $ratio = ($qtyOrdered - $invoiceQty) / $qtyOrdered;
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
             * Check other invoices, to avoid round issues and not to invoice more than the taxes
             */
            if($order->hasInvoices()){
                foreach ($order->getInvoiceCollection()->getItems() as $i){
                    /**
                     *  @var TransiteoTaxesExtensionInterface $i
                     */
                    $amountAvailableBaseTransiteoTotalTaxes -= $i->getBaseTransiteoTotalTaxes();
                    $amountAvailableTransiteoTotalTaxes -= $i->getTransiteoTotalTaxes();
                    $amountAvailableBaseTransiteoDuty -= $i->getBaseTransiteoDuty();
                    $amountAvailableTransiteoDuty -= $i->getTransiteoDuty() ;
                    $amountAvailableBaseTransiteoVat -= $i->getBaseTransiteoVat() ;
                    $amountAvailableTransiteoVat -= $i->getTransiteoVat();
                    $amountAvailableBaseTransiteoSpecialTaxes -= $i->getBaseTransiteoSpecialTaxes();
                    $amountAvailableTransiteoSpecialTaxes -= $i->getTransiteoSpecialTaxes();
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
                $baseTransiteoTotalTaxes = $invoice->roundPrice($transiteoTotalTaxes, 'base', $baseTransiteoTotalTaxes < 0);
            }
            $transiteoTotalTaxes = $invoice->roundPrice($transiteoTotalTaxes, 'regular', $transiteoTotalTaxes < 0);

            if(isset($baseTransiteoDuty)){
                $baseTransiteoDuty = $invoice->roundPrice($transiteoDuty, 'base', $baseTransiteoDuty < 0);
            }
            if(isset($transiteoDuty)){
                $transiteoDuty = $invoice->roundPrice($transiteoDuty, 'regular', $transiteoDuty < 0);
            }
            if(isset($baseTransiteoVat)){
                $baseTransiteoVat = $invoice->roundPrice($transiteoVat, 'base', $baseTransiteoVat < 0);
            }
            if(isset($transiteoVat)){
                $transiteoVat = $invoice->roundPrice($transiteoVat, 'regular', $transiteoVat < 0);
            }
            if(isset($baseTransiteoSpecialTaxes)){
                $baseTransiteoSpecialTaxes = $invoice->roundPrice($transiteoSpecialTaxes, 'base', $baseTransiteoSpecialTaxes < 0);
            }
            if(isset($transiteoSpecialTaxes)){
                $transiteoSpecialTaxes = $invoice->roundPrice($transiteoSpecialTaxes, 'regular', $transiteoSpecialTaxes < 0);
            }

            /**
             * Apply the taxes to the invoice
             */



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
