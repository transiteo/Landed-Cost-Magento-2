<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */


declare(strict_types=1);

namespace Transiteo\LandedCost\Observer\Sync\Product;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Psr\Log\LoggerInterface;
use Transiteo\LandedCost\Service\ProductSync;

class DeleteAfter implements ObserverInterface
{

    /**
     * @var ProductSync
     */
    protected $productSync;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ProductSync $productSync
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProductSync $productSync,
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
        $this->productSync = $productSync;
    }

    public function execute(Observer $observer)
    {
        /**
         * @var ProductInterface $product
         */

        try{
            $product = $observer->getProduct();
            if($product->isDeleted()){
                $this->productSync->asyncDeleteMultipleStoreValuesOfProduct((int) $product->getId());
                $this->productSync->deleteMultipleStoreValuesOfProduct((int) $product->getId());
            }
        }catch(\Exception $e){
            $this->logger->error($e);
        }
    }
}
