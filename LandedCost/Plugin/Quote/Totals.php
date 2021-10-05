<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\LandedCost\Plugin\Quote;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryInStorePickupShippingApi\Model\Carrier\InStorePickup;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\TotalsCollector;
use Transiteo\LandedCost\Model\Quote\Surcharge;

class Totals extends \Magento\Quote\Model\Quote\TotalsCollector
{

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
        foreach ($quote->getAllAddresses() as $address) {
            $addressTotal = $this->collectAddressTotals($quote, $address);

            $total->setTotalAmount(Surcharge::COLLECTOR_TYPE_CODE, (float) $addressTotal->getTotalAmount(Surcharge::COLLECTOR_TYPE_CODE));
            $total->setBaseTotalAmount(Surcharge::COLLECTOR_TYPE_CODE,(float)  $addressTotal->getBaseTotalAmount(Surcharge::COLLECTOR_TYPE_CODE));
        }
        return $total;
    }
}
