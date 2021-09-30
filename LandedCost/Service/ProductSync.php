<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\LandedCost\Service;


use Blackbird\ContentManager\Block\View\Field\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Model\StoreManagerInterface;
use Transiteo\LandedCost\Model\Config;
use Transiteo\LandedCost\Model\TransiteoApiService;

class ProductSync
{

    public const SYNC_PRODUCT_TOPIC = "transiteo.sync.product";

    /**
     * @var TransiteoApiService
     */
    protected $apiService;
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var PublisherInterface
     */
    protected $publisher;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @param TransiteoApiService $apiService
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param PublisherInterface $publisher
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        TransiteoApiService $apiService,
        Config $config,
        StoreManagerInterface $storeManager,
        PublisherInterface $publisher,
        ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    )
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->config = $config;
        $this->apiService = $apiService;
        $this->storeManager = $storeManager;
        $this->publisher = $publisher;
        $this->productRepository = $productRepository;
    }


    /**
     * @param ProductInterface $product
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createProduct(ProductInterface $product):?string
    {
        $productParams = $this->transformProductIntoParam($product, Request::HTTP_METHOD_POST);
        return $this->actionOnProduct($productParams, Request::HTTP_METHOD_POST);
    }

    /**
     * @param ProductInterface $product
     * @return string|null Return error message or null if success.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateProduct(ProductInterface $product):?string
    {
        $productParams = $this->transformProductIntoParam($product, Request::HTTP_METHOD_PUT);
        return $this->actionOnProduct($productParams, Request::HTTP_METHOD_PUT);
    }

    /**
     * @param ProductInterface $product
     * @return string|null Return error message or null if success.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteProduct(ProductInterface $product):?string
    {
        $productParams = $this->transformProductIntoParam($product, Request::HTTP_METHOD_DELETE);
        return $this->actionOnProduct($productParams, Request::HTTP_METHOD_DELETE);
    }

    /**
     * @param array $productParams
     * @param string $method
     * @return string|null Return error message or null if success.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function actionOnProduct(array $productParams, string $method):?string
    {
        $request = [
            'headers' => [
                'Content-type'     => 'application/json',
                'Authorization' => $this->apiService->getIdToken()
            ],
            'json' => $productParams
        ];

        $url = TransiteoApiService::API_REQUEST_URI . "v1/customer/products";
        if($method !== Request::HTTP_METHOD_POST){
            $url .= '/' . $productParams['sku'];
        }

        $response = $this->apiService->doRequest(
            $url,
            $request,
            $method
        );

        $status = $response->getStatusCode();

        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents();

        $responseArray = \json_decode($responseContent);


        if (($status == "401") && isset($responseArray->message) && $responseArray->message === "The incoming token has expired") {
            $this->apiService->refreshIdToken();
            return $this->actionOnProduct($productParams, $method);
        }

        if($status != "200"){
            if(isset($responseArray->message)) {
                ////LOGGER////
                $this->apiService->getLogger()->debug('Response : status => ' . $status . ' message : ' . $responseArray['message']);
                return $responseArray['message'];
            }
            if($response->getReasonPhrase()){
                return $response->getReasonPhrase();
            }
        }

        return null;
    }

    /**
     * @param ProductInterface $product
     * @param string $method
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function transformProductIntoParam(ProductInterface $product, string $method):array
    {
        //send only the product id for deleting
        if($method === Request::HTTP_METHOD_DELETE){
            return [
                'sku' => $this->config->getTransiteoProductSku($product)
            ];
        }

        $result = [
            'sku' =>  (string) $this->config->getTransiteoProductSku($product),
            'type' => 'SKU',
            'value' => (string) ($product->getName() ?? ''),
            'weight' => (float) ($product->getWeight() ?? 0.5),
            'weight_unit' => (string) $this->config->getWeightUnit($product->getStoreId()),
            'store_id' => (int) $product->getStoreId(),
            'product_id' => (int) $product->getId()
//            "unit_price" => $product->getPrice() ?? 0.0
        ];



        return $result;
    }

    /**
     * @param ProductInterface $product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function asyncCreateProduct(ProductInterface $product): void
    {
        $this->asyncActionOnProduct($product, Request::HTTP_METHOD_POST);
    }

    /**
     * @param ProductInterface $product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function asyncUpdateProduct(ProductInterface $product): void
    {
        $this->asyncActionOnProduct($product, Request::HTTP_METHOD_PUT);
    }

    /**
     * @param ProductInterface $product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function asyncDeleteProduct(ProductInterface $product): void
    {
        $this->asyncActionOnProduct($product, Request::HTTP_METHOD_DELETE);
    }

    /**
     * @param ProductInterface $product
     * @param string $method
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function asyncActionOnProduct(ProductInterface $product, string $method): void
    {
        $data = [
            'method' => $method
        ];
        try{
            $data['product'] = $this->transformProductIntoParam($product, $method);
        }catch(\Exception $e){
            $this->apiService->getLogger()->error($e);
            $data['product_id'] = $product->getId();
            $data['store_id'] = $product->getStoreId();
        }

        $message = serialize($data);
        $this->publisher->publish(self::SYNC_PRODUCT_TOPIC, $message);
    }

    /**
     * @param int $productId
     * @param int $storeId
     * @param string $method
     */
    protected function asyncActionOnProductById(int $productId, int $storeId, string $method){
        $data = [
            'method' => $method,
            'product_id' => $productId,
            'store_id' => $storeId
        ];
        $message = serialize($data);
        $this->publisher->publish(self::SYNC_PRODUCT_TOPIC, $message);
    }


    /**
     * @param int $productId
     * @param array|null $storeIds If = null, all stores values will be affected
     * @param bool $load
     */
    public function asyncCreateMultipleStoreValuesOfProduct(int $productId, ?array $storeIds = null, $load = false):void
    {
        try{
            if($load){
                foreach ($this->getStoreValuesOfProduct($productId, $storeIds) as $product){
                    $this->asyncCreateProduct($product);
                }
            }else{
                foreach(array_keys($this->storeManager->getStores(false)) as $id){
                    $this->asyncActionOnProductById($productId, $id, Request::HTTP_METHOD_POST);
                }
            }
        }catch(\Magento\Framework\Exception\NoSuchEntityException $e){
            $this->apiService->getLogger()->error($e);
        }
    }

    /**
     * @param int $productId
     * @param array|null $storeIds If = null, all stores values will be affected
     * @param bool $load load the product in async
     */
    public function asyncDeleteMultipleStoreValuesOfProduct(int $productId,?array $storeIds = null, bool $load = false):void
    {
        try{
            if($load){
                foreach ($this->getStoreValuesOfProduct($productId, $storeIds) as $product){
                    $this->asyncDeleteProduct($product);
                }
            }else{
                foreach(array_keys($this->storeManager->getStores(false)) as $id){
                    $this->asyncActionOnProductById($productId, $id, Request::HTTP_METHOD_DELETE);
                }
            }
        }catch(\Magento\Framework\Exception\NoSuchEntityException $e){
            $this->apiService->getLogger()->error($e);
        }
    }

    /**
     * @param int $productId
     * @param array|null $storeIds If = null, all stores values will be affected
     * @param bool $load load the product in async
     */
    public function asyncUpdateMultipleStoreValuesOfProduct(int $productId,?array $storeIds = null, $load = false):void
    {
        try{
            if($load){
                foreach ($this->getStoreValuesOfProduct($productId, $storeIds) as $product){
                    $this->asyncUpdateProduct($product);
                }
            }else{
                foreach(array_keys($this->storeManager->getStores(false)) as $id){
                    $this->asyncActionOnProductById($productId, (int) $id, Request::HTTP_METHOD_PUT);
                }
            }
        }catch(\Magento\Framework\Exception\NoSuchEntityException $e){
            $this->apiService->getLogger()->error($e);
        }
    }

    /**
     * @param int $productId
     * @param array|null $storeIds
     * @return ProductInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStoreValuesOfProduct(int $productId, ?array $storeIds = null):array
    {
        if(!isset($storeIds)){
            $storeIds = array_keys($this->storeManager->getStores(false));
        }
        $products = [];
        foreach ($storeIds as $storeId){
            $products[] = $this->productRepository->getById($productId, false, $storeId);
        }
        return $products;
    }


    /**
     * @param array|null $storeIds
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function asyncUpdateAllProducts(?array $storeIds = null){
        if(!isset($storeIds)){
            $storeIds = array_keys($this->storeManager->getStores(false));
        }
        /**
         * @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
         */
        $collection = $this->productCollectionFactory->create();

        $listOfRemoteProducts = $this->getListOfProducts();
        if(isset($listOfRemoteProducts) && is_array($listOfRemoteProducts)){
            $remoteIds = array_column($listOfRemoteProducts,'product_id');

            $productIds = $collection->getAllIds();

            //create new products
            $newProducts = \array_diff($productIds, $remoteIds);
            foreach ($newProducts as $productId){
                $this->asyncCreateMultipleStoreValuesOfProduct((int) $productId, $storeIds);
            }

            //update products
            $updatedProducts = \array_diff($productIds, $newProducts);
            foreach ($updatedProducts as $productId){
                $this->asyncUpdateMultipleStoreValuesOfProduct((int) $productId, $storeIds);
            }

            //delete products
            $deletedProducts = \array_diff($remoteIds, $productIds);
            $listOfRemoteSkus = [];
            if(!empty($deletedProducts)){
                foreach ($listOfRemoteProducts as $product){
                    if(\in_array($product->product_id ?? null, $deletedProducts, true)){
                        $listOfRemoteSkus[] = $product->sku ?? '';
                    }
                }
                foreach ($listOfRemoteSkus as $sku){
                    $this->asyncDeleteBySku($sku);
                }
            }
        }
    }

    /**
     * @return array|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getListOfProducts():?array{
        $request = [
            'headers' => [
                'Content-type'     => 'application/json',
                'Authorization' => $this->apiService->getIdToken()
            ]
        ];

        $url = TransiteoApiService::API_REQUEST_URI . "v1/customer/products";

        $response = $this->apiService->doRequest(
            $url,
            $request,
            Request::HTTP_METHOD_GET
        );

        $status = $response->getStatusCode();

        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents();

        $responseArray = \json_decode($responseContent);


        if (($status == "401") && isset($responseArray->message) && $responseArray->message === "The incoming token has expired") {
            $this->apiService->refreshIdToken();
            return $this->getListOfProducts();
        }

        if($status != "200"){
            if(isset($responseArray->message)) {
                ////LOGGER////
                $this->apiService->getLogger()->debug('Response : status => ' . $status . ' message : ' . $responseArray['message']);
                return null;
            }
            if($response->getReasonPhrase()){
                return null;
            }
        }

        return (array) $responseArray;
    }

    /**
     * @param $sku
     */
    protected function asyncDeleteBySku($sku)
    {
        $data = [
            'method' => Request::HTTP_METHOD_DELETE,
            'product' => ['sku' => $sku]
        ];
        $message = serialize($data);
        $this->publisher->publish(self::SYNC_PRODUCT_TOPIC, $message);
    }

}
