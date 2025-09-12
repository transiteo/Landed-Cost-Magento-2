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

namespace Transiteo\LandedCost\Observer;

class SaveTaxesToOrder implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Transiteo\LandedCost\Model\Config
     */
    protected $config;

    public function __construct(\Transiteo\LandedCost\Model\Config $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->config->isEnabled()) {
            return $this;
        }
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
