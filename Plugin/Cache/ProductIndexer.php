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
namespace Transiteo\LandedCost\Plugin\Cache;

use \Magento\Catalog\Model\Indexer\Category\Product;
use Transiteo\LandedCost\Model\Cache\Handler\Taxes;

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
