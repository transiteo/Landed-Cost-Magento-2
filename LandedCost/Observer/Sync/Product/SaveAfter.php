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
use Psr\Log\LoggerInterface;
use Transiteo\LandedCost\Service\ProductSync;

class SaveAfter implements ObserverInterface
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
            if($product->isObjectNew()){
                if($product->getStoreId() === 0){
                    $this->productSync->asyncCreateMultipleStoreValuesOfProduct((int) $product->getId());
                    $this->productSync->createMultipleStoreValuesOfProduct((int) $product->getId());
                }else{
                    $this->productSync->asyncCreateProduct($product);
                    $this->productSync->createProduct($product);
                }
            }else if($product->hasDataChanges()){
                if($product->getStoreId() === 0){
                    $this->productSync->asyncUpdateMultipleStoreValuesOfProduct((int) $product->getId());
                    $this->productSync->updateMultipleStoreValuesOfProduct((int) $product->getId());
                }else{
                    $this->productSync->asyncUpdateProduct($product);
                    $this->productSync->updateProduct($product);
                }
            }
        }catch(\Exception $e){
            $this->logger->error($e);
        }

    }
}
