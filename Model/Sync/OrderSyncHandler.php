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

namespace Transiteo\LandedCost\Model\Sync;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Webapi\Rest\Request;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 *
 */
class OrderSyncHandler
{
    /**
     * @var \Transiteo\LandedCost\Model\Config
     */
    protected $config;

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
        OrderRepositoryInterface $orderRepository,
        \Transiteo\LandedCost\Model\Config $config
    ) {
        $this->orderRepository = $orderRepository;
        $this->config = $config;
        $this->logger = new Logger('custom');
        $logFile = BP . '/var/log/test.log';
        $logger->pushHandler(new StreamHandler($logFile, Logger::INFO));
        $this->orderSync = $orderSync;
    }

    /**
     * @param string $message
     * @throws \Exception
     */
    public function process(string $message)
    {
        if (!$this->config->isSyncOrderEnabled()) {
            return;
        }
        try {
            $params = unserialize($message);

            //////////////////LOGGER//////////////
            $result = json_encode($params);
            $this->logger->info($result);
            ///////////////////////////////////////

            $method = $params["method"];
            $errorMessage = null;
            if (array_key_exists("order", $params)) {
                $order = $params["order"];
            } else {
                $orderModel = $this->orderRepository->get((int)$params['order_id']);
                $order = $this->orderSync->transformOrderIntoParam($orderModel, $method);
            }

            $errorMessage = $this->orderSync->actionOnOrder($order, $method);
            $this->logger->debug($errorMessage);

            //if the order does not exist, create it.
            if ($errorMessage && $method === Request::HTTP_METHOD_PUT) {
                if (!isset($orderModel)) {
                    $orderModel = $this->orderRepository->get((int)$params['order_id']);
                }
                $order = $this->orderSync->transformOrderIntoParam($orderModel, Request::HTTP_METHOD_POST);
                $errorMessage = $this->orderSync->actionOnOrder($order, Request::HTTP_METHOD_POST);
            }
            if ($errorMessage) {
                $message = "Error in response from Api, error : " . $errorMessage . "  in message " . $message . " with request : " . $message;
                $this->logger->debug($errorMessage);
                $requestParams = \json_encode($order);
                throw new \Exception(
                    "Error in response from Api, error : " . $errorMessage . "  in message " . $message . " with request : " . $requestParams
                );
            }
        } catch (\Exception $exception) {
            //////////////////LOGGER//////////////
            $this->logger->error($exception->getTraceAsString());
            throw $exception;
        }
    }
}
