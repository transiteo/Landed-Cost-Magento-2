<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\LandedCost\Plugin\Quote;

class TaxesToOrderItem
{
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
        $orderItem->setTaxAmount($item->getTaxAmount());
        $orderItem->setBaseTaxAmount($item->getBaseTaxAmount());
        $orderItem->setTaxPercent($item->getTaxPercent());
        return $orderItem;
    }
}
