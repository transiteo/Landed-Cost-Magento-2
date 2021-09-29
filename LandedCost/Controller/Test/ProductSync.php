<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

declare(strict_types=1);

namespace Transiteo\LandedCost\Controller\Test;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ProductSync implements ActionInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var \Transiteo\LandedCost\Service\ProductSync
     */
    protected $productSync;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param RequestInterface $request
     * @param \Transiteo\LandedCost\Service\ProductSync $productSync
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        RequestInterface $request,
        \Transiteo\LandedCost\Service\ProductSync $productSync,
        ProductRepositoryInterface $productRepository
    )
    {
        $this->request = $request;
        $this->productSync = $productSync;
        $this->productRepository = $productRepository;
    }

    public function execute()
    {
        $id = $this->request->getParam('id');
        $product = $this->productRepository->getById($id);
        $this->productSync->asyncCreateMultipleStoreValuesOfProduct((int) $product->getId());
//        $product->setStatus('hello');
        $product->setName("HELLO");
//        $this->productSync->asyncUpdateProduct($product);
//        $this->productSync->asyncDeleteProduct($product);
        exit("Test");
    }

}

