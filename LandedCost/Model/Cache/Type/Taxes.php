<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

declare(strict_types=1);

namespace Transiteo\LandedCost\Model\Cache\Type;


class Taxes extends \Magento\Framework\Cache\Frontend\Decorator\TagScope
{
    /**
     * The recommended Cache Lifetime to Apply
     */
    public const DEFAULT_CACHE_LIFETIME = 3600;

    /**
     * Cache type code unique among all cache types
     */
    public const TYPE_IDENTIFIER = 'transiteo_taxes';

    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    public const CACHE_TAG = 'TRANSITEO_TAXES';

    /**
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool
     */
    public function __construct(\Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool)
    {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
}
