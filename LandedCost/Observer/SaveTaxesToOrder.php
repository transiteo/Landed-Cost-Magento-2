<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\LandedCost\Observer;

class SaveTaxesToOrder implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @inheritDoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $quote = $observer->getQuote();
        if ($order && $quote) {
            $order->setTransiteoIncoterm($quote->getTransiteoIncoterm());
            $order->setTransiteoVat($quote->getTransiteoVat());
            $order->setTransiteoDuty($quote->getTransiteoDuty());
            $order->setTransiteoSpecialTaxes($quote->getTransiteoSpecialTaxes());
            $order->setTransiteoTotalTaxes($quote->getTransiteoTotalTaxes());
            $order->setBaseTransiteoVat($quote->getBaseTransiteoVat());
            $order->setBaseTransiteoDuty($quote->getBaseTransiteoDuty());
            $order->setBaseTransiteoSpecialTaxes($quote->getBaseTransiteoSpecialTaxes());
            $order->setBaseTransiteoTotalTaxes($quote->getBaseTransiteoTotalTaxes());

        }

        return $this;
    }
}
