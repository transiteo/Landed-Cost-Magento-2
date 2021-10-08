<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\LandedCost\Plugin\Quote;

use Magento\Sales\Api\Data\OrderItemInterface;
use Transiteo\LandedCost\Model\Currency\Import\Transiteo;

class TaxesToOrderItem
{
    /**
     * @var \Transiteo\LandedCost\Model\Config
     */
    protected $config;

    public function __construct(
        \Transiteo\LandedCost\Model\Config $config
    )
    {
        $this->config = $config;
    }

    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = []
    ) {
        $orderItem = $proceed($item, $additional);
        $orderItem->setTransiteoVat($item->getTransiteoVat());
        $orderItem->setTransiteoDuty($item->getTransiteoDuty());
        $orderItem->setTransiteoSpecialTaxes($item->getTransiteoSpecialTaxes());
        $orderItem->setTransiteoTotalTaxes($item->getTransiteoTotalTaxes());
        $orderItem->setBaseTransiteoVat($item->getBaseTransiteoVat());
        $orderItem->setBaseTransiteoDuty($item->getBaseTransiteoDuty());
        $orderItem->setBaseTransiteoSpecialTaxes($item->getBaseTransiteoSpecialTaxes());
        $orderItem->setBaseTransiteoTotalTaxes($item->getBaseTransiteoTotalTaxes());



        //add tax compensation to avoid adding the amount to the row total
        /**
         * @var OrderItemInterface $orderItem
         */

        $totalTaxes = $orderItem->getTransiteoTotalTaxes();
        if(isset($totalTaxes)){
            $orderItem->setTaxAmount($item->getTaxAmount());
            $orderItem->setBaseTaxAmount($item->getBaseTaxAmount());
            $orderItem->setTaxPercent($item->getTaxPercent());
//            $this->applyTaxesOnOrderItem($orderItem);
        }


        return $orderItem;
    }

    /**
     * @param OrderItemInterface $orderItem
     * @return OrderItemInterface
     */
    protected function applyTaxesOnOrderItem(OrderItemInterface $orderItem):OrderItemInterface
    {
        $totalTaxes = $orderItem->getTransiteoTotalTaxes();
        $baseTotalTaxes = $orderItem->getBaseTransiteoTotalTaxes();
        $rowTotal = $orderItem->getRowTotal();
        $baseRowTotal = $orderItem->getBaseRowTotal();

        //set Taxe amount in row
        if($this->config->isDDPActivated() && ! $this->config->getIsPriceIncludingTaxes()){
            $orderItem->setTaxAmount($orderItem->getTransiteoTotalTaxes());
            if(isset($baseTotalTaxes)){
                $orderItem->setBaseTaxAmount($orderItem->getBaseTransiteoTotalTaxes());
            }
        }

        $qty = $orderItem->getQtyOrdered();
        $deltaDiscount = $orderItem->getDiscountAmount() / $qty ;
        $unitTaxes = ($totalTaxes / $qty);

        if(isset($baseTotalTaxes)){
            $baseUnitTaxes = ($baseTotalTaxes / $qty);
            $baseDeltaDiscount = $orderItem->getBaseDiscountAmount() / $qty;
        }
        if($this->config->getIsPriceIncludingTaxes()){
            $priceIncTaxes = $orderItem->getPrice() - $deltaDiscount;
            $priceExcTaxes = $priceIncTaxes - $unitTaxes ;
            $orderItem->setPriceInclTax($priceIncTaxes);

            if(isset($baseTotalTaxes)){
                $basePriceInclTaxes = $orderItem->getBasePrice() - $baseDeltaDiscount;
//            $basePriceExcTaxes = $basePriceInclTaxes - $baseUnitTaxes ;
            }
        }else{
            $priceExcTaxes = $orderItem->getPrice() - $deltaDiscount ;
            $priceIncTaxes = $priceExcTaxes + $unitTaxes;


            if(isset($baseTotalTaxes)){
                $basePriceExcTaxes = $orderItem->getBasePrice() - $baseDeltaDiscount;
                $basePriceInclTaxes = $basePriceExcTaxes + $baseUnitTaxes ;
            }
        }
        $orderItem->setPriceInclTax($priceIncTaxes);

        if(isset($baseTotalTaxes)) {
            $orderItem->setBasePriceInclTax($basePriceInclTaxes);
        }

        $taxPercent = $unitTaxes / $priceExcTaxes;
        $orderItem->setTaxPercent($taxPercent);

        if($this->config->isDDPActivated()){
            if(!$this->config->getIsPriceIncludingTaxes()){
                $orderItem->setRowTotalInclTax($rowTotal + $totalTaxes);

                if(isset($baseTotalTaxes)){
                    $orderItem->setBaseRowTotalInclTax($baseRowTotal + $baseTotalTaxes);
                }
            }

        }else{
            $orderItem->setRowTotalInclTax($rowTotal);

            if(isset($baseTotalTaxes)){
                $orderItem->setBaseRowTotalInclTax($baseRowTotal);
            }
        }
        return $orderItem;
    }
}
