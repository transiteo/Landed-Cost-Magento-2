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
