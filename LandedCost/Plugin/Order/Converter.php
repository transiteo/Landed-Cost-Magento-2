<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\LandedCost\Plugin\Order;

use Magento\Sales\Model\Convert\Order;

class Converter
{


    /**
     * @param Order $subject
     * @param \Closure $proceed
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return mixed
     */
    public function aroundItemToInvoiceItem(
        \Magento\Sales\Model\Convert\Order $subject,
        \Closure $proceed,
        \Magento\Sales\Model\Order\Item $orderItem
    ) {
        $entityItem = $proceed($orderItem);
        return $this->applyOrderTaxesToEntityItem($orderItem, $entityItem);
    }

    /**
     * @param Order $subject
     * @param \Closure $proceed
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return mixed
     */
    public function aroundItemToShipmentItem(
        \Magento\Sales\Model\Convert\Order $subject,
        \Closure $proceed,
        \Magento\Sales\Model\Order\Item $orderItem
    ) {
        $entityItem = $proceed($orderItem);
        return $this->applyOrderTaxesToEntityItem($orderItem, $entityItem);
    }

    /**
     * @param Order $subject
     * @param \Closure $proceed
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return mixed
     */
    public function aroundItemToCreditmemoItem(
        \Magento\Sales\Model\Convert\Order $subject,
        \Closure $proceed,
        \Magento\Sales\Model\Order\Item $orderItem
    ) {
        $entityItem = $proceed($orderItem);
        return $this->applyOrderTaxesToEntityItem($orderItem, $entityItem);
    }

    /**
     * @param $orderItem
     * @param $entityItem
     * @return mixed
     */
    protected function applyOrderTaxesToEntityItem($orderItem, $entityItem)
    {
        $entityItem->setTransiteoVat($orderItem->getTransiteoVat());
        $entityItem->setTransiteoDuty($orderItem->getTransiteoDuty());
        $entityItem->setTransiteoSpecialTaxes($orderItem->getTransiteoSpecialTaxes());
        $entityItem->setTransiteoTotalTaxes($orderItem->getTransiteoTotalTaxes());
        $entityItem->setBaseTransiteoVat($orderItem->getBaseTransiteoVat());
        $entityItem->setBaseTransiteoDuty($orderItem->getBaseTransiteoDuty());
        $entityItem->setBaseTransiteoSpecialTaxes($orderItem->getBaseTransiteoSpecialTaxes());
        $entityItem->setBaseTransiteoTotalTaxes($orderItem->getBaseTransiteoTotalTaxes());
        $entityItem->setTaxAmount($orderItem->getTaxAmount());
        $entityItem->setBaseTaxAmount($orderItem->getBaseTaxAmount());
        $entityItem->setTaxPercent($orderItem->getTaxPercent());
        return $entityItem;
    }

}
