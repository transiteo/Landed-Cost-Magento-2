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
use Magento\Catalog\Api\Data\ProductInterface;
use Psr\Log\LoggerInterface;
use Transiteo\LandedCost\Service\ProductSync;

class DeleteAfter implements ObserverInterface
{

    /**
     * @var \Transiteo\LandedCost\Model\Config
     */
    protected $config;

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
        LoggerInterface $logger,
        \Transiteo\LandedCost\Model\Config $config
    )
    {
        $this->logger = $logger;
        $this->productSync = $productSync;
        $this->config = $config;
    }

    public function execute(Observer $observer)
    {
        if (!$this->config->isEnabled()) {
            return;
        }
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
