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

namespace Transiteo\LandedCost\Model\Sync;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Webapi\Rest\Request;

/**
 *
 */
class ProductSyncHandler
{

    /**
     * @var  \Transiteo\LandedCost\Logger\QueueLogger
     */
    protected $logger;
    /**
     * @var \Transiteo\LandedCost\Service\ProductSync
     */
    protected $productSync;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param  \Transiteo\LandedCost\Logger\QueueLogger $logger
     * @param \Transiteo\LandedCost\Service\ProductSync $productSync
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Transiteo\LandedCost\Logger\QueueLogger $logger,
        \Transiteo\LandedCost\Service\ProductSync $productSync,
        ProductRepositoryInterface $productRepository
    )
    {
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->productSync = $productSync;
    }

    /**
     * @param string $message
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function process(string $message)
    {
        try {
            $params = unserialize($message);
            $method = $params["method"];
            $errorMessage = null;
            if(array_key_exists("product", $params)) {
                $product = $params["product"];
            }else {
                $productModel = $this->productRepository->getById((int) $params['product_id'],false,(int) $params['store_id']);
                $product = $this->productSync->transformProductIntoParam($productModel, $method);
            }
                $errorMessage = $this->productSync->actionOnProduct($product, $method);
                //if the product does not exist, create it.
                if($errorMessage && $method === Request::HTTP_METHOD_PUT){
                    if(!isset($productModel)){
                        $productModel = $this->productRepository->getById((int) $params['product_id'],false,(int) $params['store_id']);
                    }
                    $product = $this->productSync->transformProductIntoParam($productModel, Request::HTTP_METHOD_POST);
                    $errorMessage = $this->productSync->actionOnProduct($product, Request::HTTP_METHOD_POST);
                }
            if($errorMessage) {
                $requestParams =  \json_encode($product);
                $message = "Error in response from Api, error : " . $errorMessage . "  in message " . $message . " with request : " . $requestParams;
                $this->logger->debug($message);
                throw new \Exception($message);
            }
        }catch (\Exception $exception){
            $this->logger->error($exception);
            throw $exception;
        }
    }
}
