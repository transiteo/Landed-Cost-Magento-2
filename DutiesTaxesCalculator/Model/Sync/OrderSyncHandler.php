<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

declare(strict_types=1);

namespace Transiteo\DutiesTaxesCalculator\Model\Sync;

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
     * @var \Transiteo\DutiesTaxesCalculator\Service\OrderSync
     */
    protected $orderSync;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Transiteo\DutiesTaxesCalculator\Service\OrderSync $orderSync
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Transiteo\DutiesTaxesCalculator\Service\OrderSync $orderSync
    )
    {
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
            $order = $params["order"];
            if(!$this->orderSync->actionOnOrder($order, $method)) {
                throw new \Exception("Error in response from Api");
            }
        }catch (\Exception $exception){
            $this->logger->error($exception);
            throw $exception;
        }
    }
}
