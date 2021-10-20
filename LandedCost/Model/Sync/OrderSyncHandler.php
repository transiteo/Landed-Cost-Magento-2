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
     * @var \Transiteo\LandedCost\Logger\Logger
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
     * @param \Transiteo\LandedCost\Logger\Logger $logger
     * @param \Transiteo\LandedCost\Service\OrderSync $orderSync
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Transiteo\LandedCost\Logger\QueueLogger $logger,
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
            //////////////////LOGGER//////////////
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $result = \json_encode($params);
            $logger->info($result);
            ///////////////////////////////////////
            $method = $params["method"];
            $errorMessage = null;
            if(array_key_exists("order", $params)) {
                $order = $params["order"];
            } else{
                $orderModel = $this->orderRepository->get((int) $params['order_id']);
                $order = $this->orderSync->transformOrderIntoParam($orderModel, $method);
            }

            $errorMessage = $this->orderSync->actionOnOrder($order, $method);
            //if the order does not exist, create it.
            if($errorMessage && $method === Request::HTTP_METHOD_PUT){
                if(!isset($orderModel)){
                    $orderModel = $this->orderRepository->get((int) $params['order_id']);
                }
                $order = $this->orderSync->transformOrderIntoParam($orderModel, Request::HTTP_METHOD_POST);
                $errorMessage = $this->orderSync->actionOnOrder($order, Request::HTTP_METHOD_POST);
            }
            if($errorMessage) {
                $message = "Error in response from Api, error : " . $errorMessage . "  in message " . $message . " with request : " . $requestParams;
                $this->logger->debug($message);
                $requestParams =  \json_encode($order);
                throw new \Exception("Error in response from Api, error : " . $errorMessage . "  in message " . $message . " with request : " . $requestParams);
            }
        }catch (\Exception $exception){
            $this->logger->debug($exception);
            throw $exception;
        }
    }
}
