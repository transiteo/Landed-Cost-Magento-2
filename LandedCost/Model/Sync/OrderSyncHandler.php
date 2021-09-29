<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

declare(strict_types=1);

namespace Transiteo\LandedCost\Model\Sync;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Webapi\Rest\Request;

/**
 *
 */
class OrderSyncHandler
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var \Transiteo\LandedCost\Service\OrderSync
     */
    protected $orderSync;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Transiteo\LandedCost\Service\OrderSync $orderSync
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Transiteo\LandedCost\Service\OrderSync $orderSync,
        OrderRepositoryInterface $orderRepository
    )
    {
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->orderSync = $orderSync;
    }

    /**
     * @param string $message
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function process(string $message)
    {
        try {
            $params = unserialize($message);
            $method = $params["method"];
            $valid = false;
            if(array_key_exists("order", $params)){
                $order = $params["order"];
                $valid = $this->orderSync->actionOnOrder($order, $method);
            }else{
                $orderModel = $this->orderRepository->get($params['order_id']);
                if($method === Request::HTTP_METHOD_DELETE){
                    $valid = $this->orderSync->deleteOrder($orderModel);
                }
                if($method === Request::HTTP_METHOD_POST){
                    $valid = $this->orderSync->createOrder($orderModel);
                }
                if($method === Request::HTTP_METHOD_PUT){
                    $valid = $this->orderSync->deleteOrder($orderModel);
                }
            }
            if(!$valid) {
                throw new \Exception("Error in response from Api");
            }
        }catch (\Exception $exception){
            $this->logger->error($exception);
            throw $exception;
        }
    }
}
