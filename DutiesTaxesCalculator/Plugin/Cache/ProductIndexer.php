<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

declare(strict_types=1);
namespace Transiteo\DutiesTaxesCalculator\Plugin\Cache;

use \Magento\Catalog\Model\Indexer\Category\Product;
use Transiteo\DutiesTaxesCalculator\Model\Cache\Handler\Taxes;

class ProductIndexer
{
    /**
     * @var Taxes
     */
    protected $taxesCacheHandler;

    /**
     * @param Taxes $taxesCacheHandler
     */
    public function __construct(
        Taxes $taxesCacheHandler
    )
    {
        $this->taxesCacheHandler = $taxesCacheHandler;
    }

    /**
     * @param Product $subject
     * @param $result
     */
    public function afterExecuteFull(Product $subject, $result)
    {
        $this->taxesCacheHandler->flushCache();
    }

    /**
     * @param Product $subject
     * @param $result
     * @param $ids
     */
    public function afterExecute(Product $subject,$result, $ids){
        $this->taxesCacheHandler->flushCacheByProductIds($ids);
    }

    /**
     * @param Product $subject
     * @param $result
     * @param $ids
     */
    public function afterExecuteList(Product $subject,$result, $ids){
        $this->taxesCacheHandler->flushCacheByProductIds($ids);
    }

    /**
     * @param Product $subject
     * @param $result
     * @param $id
     */
    public function afterExecuteRow(Product $subject,$result, $id){
        $this->taxesCacheHandler->flushCacheByProductIds([$id]);
    }
}
