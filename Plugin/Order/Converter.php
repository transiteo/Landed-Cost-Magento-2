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
