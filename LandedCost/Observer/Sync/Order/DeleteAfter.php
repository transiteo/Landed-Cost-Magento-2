<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
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
                $this->orderSync->createOrder($order);
            }
        }catch(\Exception $e){
            $this->logger->error($e);
        }
    }
}
