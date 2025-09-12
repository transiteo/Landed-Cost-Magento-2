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

namespace Transiteo\LandedCost\Plugin\Quote;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\Collector;
use Magento\Quote\Model\Quote\Address\Total\CollectorFactory;
use Magento\Quote\Model\Quote\QuantityCollector;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\Quote\TotalsCollectorList;
use Transiteo\LandedCost\Model\Quote\Surcharge;

class Totals extends \Magento\Quote\Model\Quote\TotalsCollector
{

    public function __construct(
        protected \Transiteo\LandedCost\Model\Config $config,
        Collector $totalCollector,
        CollectorFactory $totalCollectorFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\Quote\Address\TotalFactory $totalFactory,
        TotalsCollectorList $collectorList,
        \Magento\Quote\Model\ShippingFactory $shippingFactory,
        \Magento\Quote\Model\ShippingAssignmentFactory $shippingAssignmentFactory,
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        QuantityCollector $quantityCollector = null
    )
    {
        parent::__construct($totalCollector, $totalCollectorFactory, $eventManager, $storeManager, $totalFactory, $collectorList, $shippingFactory, $shippingAssignmentFactory, $quoteValidator, $quantityCollector);
    }

    /**
     * Adding Transiteo Totals
     *
     * @param TotalsCollector $subject
     * @param Total $total
     * @param Quote $quote
     * @return Total
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCollect(
        TotalsCollector $subject,
        Total $total,
        Quote $quote
    ) {
        if (!$this->config->isEnabled()) {
            return $total;
        }
        foreach ($quote->getAllAddresses() as $address) {
            $addressTotal = $this->collectAddressTotals($quote, $address);

            $total->setTotalAmount(Surcharge::COLLECTOR_TYPE_CODE, (float) $addressTotal->getTotalAmount(Surcharge::COLLECTOR_TYPE_CODE));
            $total->setBaseTotalAmount(Surcharge::COLLECTOR_TYPE_CODE,(float)  $addressTotal->getBaseTotalAmount(Surcharge::COLLECTOR_TYPE_CODE));
        }
        return $total;
    }
}
