<?php

namespace Transiteo\Taxes\Observer;

class SaveTaxesToOrder implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @inheritDoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $quote = $observer->getQuote();
        //////////////////LOGGER//////////////
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('Observer Run');
        ///////////////////////////////////////
        if ($order && $quote) {
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
