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

namespace Transiteo\LandedCost\Model\Cache\Handler;

use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;

class Taxes
{
    /**
     * @var CacheInterface
     */
    protected $cache;
    /**
     * @var SerializerInterface
     */
    protected $serializer;
    /**
     * @var StateInterface
     */
    protected $cacheState;

    /**
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @param StateInterface $state
     */
    public function __construct(
        CacheInterface $cache,
        SerializerInterface $serializer,
        StateInterface  $state
    )
    {
        $this->cacheState = $state;
        $this->cache = $cache;
        $this->serializer = $serializer;
    }


    /**
     * @param $cacheKey
     * @return array|null
     */
    public function loadFromCache($cacheKey):?array
    {
        if(!$this->cacheState->isEnabled(\Transiteo\LandedCost\Model\Cache\Type\Taxes::TYPE_IDENTIFIER)){
            return null;
        }

        $data = $this->cache->load($cacheKey);
        if($data){
            return $this->serializer->unserialize($this->cache->load($cacheKey));
        }
        return null;
    }

    /**
     * @param array $request
     * @return string
     */
    public function getKeyFromRequest(array $request):string
    {
        $cacheKey = "";
        foreach ($request as $key => $value){
            if(isset($key)){
                if(is_array($value)){
                    $cacheKey .= '--' .$key;
                    foreach ($value as $v){
                        $cacheKey .= '-' . $v;
                    }
                }else{
                    $cacheKey .= '-' .$value;
                }
            }
        }
        return $cacheKey;
    }

    /**
     * @param string $cacheKey
     * @param array $data
     * @param array $productIds
     * @return bool
     */
    public function storeToCache(string $cacheKey,array $data, array $productIds = []):bool
    {
        if(!$this->cacheState->isEnabled(\Transiteo\LandedCost\Model\Cache\Type\Taxes::TYPE_IDENTIFIER)){
            return false;
        }

        //store product sku as tags
        $tags = [\Transiteo\LandedCost\Model\Cache\Type\Taxes::CACHE_TAG];
        if(!empty($productIds)){
            foreach ($productIds as $id){
                $tags[] = \Transiteo\LandedCost\Model\Cache\Type\Taxes::CACHE_TAG . '_' . $id;
            }
        }

        return $this->cache->save(
            $this->serializer->serialize($data),
            $cacheKey,
            $tags,
            \Transiteo\LandedCost\Model\Cache\Type\Taxes::DEFAULT_CACHE_LIFETIME
        );
    }

    /**
     * @param string $cacheKey
     * @return bool
     */
    public function removeFromCache(string $cacheKey): bool
    {
        return $this->cache->remove($cacheKey);
    }

    /**
     * Flush All Taxes Cache
     * @return bool
     */
    public function flushCache():bool
    {
       return $this->cache->clean([\Transiteo\LandedCost\Model\Cache\Type\Taxes::CACHE_TAG]);
    }

    /**
     * Flush Taxes Cache By Product Id
     * @param array $ids
     * @return void
     */
    public function flushCacheByProductIds(array $ids):void
    {
        foreach ($ids as $id){
            $this->cache->clean(\Transiteo\LandedCost\Model\Cache\Type\Taxes::CACHE_TAG . '_PROD_ID_' . $id);
        }
    }
}
