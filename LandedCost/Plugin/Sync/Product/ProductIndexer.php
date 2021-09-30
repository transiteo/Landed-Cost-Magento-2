<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

declare(strict_types=1);
namespace Transiteo\LandedCost\Plugin\Sync\Product;

use \Magento\Catalog\Model\Indexer\Category\Product;
use Transiteo\LandedCost\Service\ProductSync;

class ProductIndexer
{
    /**
     * @var ProductSync
     */
    protected $productSync;

    /**
     * @param ProductSync $productSync
     */
    public function __construct(
        ProductSync $productSync
    )
    {
        $this->productSync = $productSync;
    }

    /**
     * @param Product $subject
     * @param $result
     */
    public function afterExecuteFull(Product $subject, $result)
    {
        $this->productSync->asyncUpdateAllProducts();
    }

    /**
     * @param Product $subject
     * @param $result
     * @param $ids
     */
    public function afterExecute(Product $subject,$result, $ids){
        foreach ($ids as $id){
            $this->productSync->asyncUpdateMultipleStoreValuesOfProduct((int) $id);
        }
    }

    /**
     * @param Product $subject
     * @param $result
     * @param $ids
     */
    public function afterExecuteList(Product $subject,$result, $ids){
        foreach ($ids as $id){
            $this->productSync->asyncUpdateMultipleStoreValuesOfProduct((int) $id);
        }
    }

    /**
     * @param Product $subject
     * @param $result
     * @param $id
     */
    public function afterExecuteRow(Product $subject,$result, $id){
        $this->productSync->asyncUpdateMultipleStoreValuesOfProduct((int) $id);
    }
}
