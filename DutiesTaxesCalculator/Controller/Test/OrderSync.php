<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

declare(strict_types=1);

namespace Transiteo\DutiesTaxesCalculator\Controller\Test;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderSync implements ActionInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var \Transiteo\DutiesTaxesCalculator\Service\OrderSync
     */
    protected $orderSync;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param RequestInterface $request
     * @param \Transiteo\DutiesTaxesCalculator\Service\OrderSync $orderSync
     */
    public function __construct(
        RequestInterface $request,
        \Transiteo\DutiesTaxesCalculator\Service\OrderSync $orderSync,
        OrderRepositoryInterface $orderRepository
    )
    {
        $this->request = $request;
        $this->orderSync = $orderSync;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        $id = $this->request->getParam('id');
        $order = $this->orderRepository->get($id);
        $this->orderSync->AsyncCreateOrder($order);
        $order->setStatus('hello');
//        $this->orderSync->AsyncUpdateOrder($order);
//        $this->orderSync->AsyncDeleteOrder($order);
        exit("Test");
    }

}

