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
    protected \Transiteo\LandedCost\Model\Config $config;

    /**
     * @param Taxes $taxesCacheHandler
     */
    public function __construct(
        Taxes $taxesCacheHandler,
        \Transiteo\LandedCost\Model\Config $config
    )
    {
        $this->taxesCacheHandler = $taxesCacheHandler;
        $this->config = $config;
    }

    /**
     * @param Product $subject
     * @param $result
     */
    public function afterExecuteFull(Product $subject, $result)
    {
        if (!$this->config->isEnabled()) {
            return $result;
        }
        $this->taxesCacheHandler->flushCache();
        return $result;
    }

    /**
     * @param Product $subject
     * @param $result
     * @param $ids
     */
    public function afterExecute(Product $subject,$result, $ids){
        if (!$this->config->isEnabled()) {
            return $result;
        }
        $this->taxesCacheHandler->flushCacheByProductIds($ids);
        return $result;
    }

    /**
     * @param Product $subject
     * @param $result
     * @param $ids
     */
    public function afterExecuteList(Product $subject,$result, $ids){
        if (!$this->config->isEnabled()) {
            return $result;
        }
        $this->taxesCacheHandler->flushCacheByProductIds($ids);
        return $result;
    }

    /**
     * @param Product $subject
     * @param $result
     * @param $id
     */
    public function afterExecuteRow(Product $subject,$result, $id){
        if (!$this->config->isEnabled()) {
            return $result;
        }
        $this->taxesCacheHandler->flushCacheByProductIds([$id]);
        return $result;
    }
}
