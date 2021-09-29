<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
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
     * @var \Psr\Log\LoggerInterface
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
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Transiteo\LandedCost\Service\ProductSync $productSync
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
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
            $valid = false;
            if(array_key_exists("product", $params)){
                $product = $params["product"];
                $valid = $this->productSync->actionOnProduct($product, $method);
                //if the product does not exist, create it.
                if(!$valid && $method === Request::HTTP_METHOD_PUT){
                    $valid = $this->productSync->actionOnProduct($product, Request::HTTP_METHOD_POST);
                }
            }else{
                $productModel = $this->productRepository->getById($params['product_id'],false, $params['store_id']);
                if($method === Request::HTTP_METHOD_DELETE){
                    $valid = $this->productSync->deleteProduct($productModel);
                }
                if($method === Request::HTTP_METHOD_POST){
                    $valid = $this->productSync->createProduct($productModel);
                }
                if($method === Request::HTTP_METHOD_PUT){
                    $valid = $this->productSync->updateProduct($productModel);
                    //if the product does not exist, create it.
                    if(!$valid){
                        $this->productSync->createProduct($productModel);
                    }
                }
            }
            if(!$valid) {
                throw new \Exception("Error in response from Api");
            }
        }catch (\Exception $exception){
            $this->logger->error($exception);
            throw $exception;
        }
    }
}
