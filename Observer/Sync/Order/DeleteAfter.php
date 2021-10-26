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


declare(strict_types=1);

namespace Transiteo\LandedCost\Observer\Sync\Order;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Transiteo\LandedCost\Service\OrderSync;

class DeleteAfter implements ObserverInterface
{

    /**
     * @var OrderSync
     */
    protected $orderSync;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param OrderSync $orderSync
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderSync $orderSync,
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
        $this->orderSync = $orderSync;
    }

    public function execute(Observer $observer)
    {
        /**
         * @var Order $order
         */

        try{
            $order = $observer->getOrder();
            if($order->isDeleted()){
                $this->orderSync->asyncDeleteOrder($order);
            }
        }catch(\Exception $e){
            $this->logger->error($e);
        }
    }
}
