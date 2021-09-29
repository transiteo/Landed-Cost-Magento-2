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
     * @param TransiteoApiService $apiService
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param PublisherInterface $publisher
     */
    public function __construct(
        TransiteoApiService $apiService,
        Config $config,
        StoreManagerInterface $storeManager,
        PublisherInterface $publisher,
        ProductRepositoryInterface $productRepository
    )
    {
        $this->config = $config;
        $this->apiService = $apiService;
        $this->storeManager = $storeManager;
        $this->publisher = $publisher;
        $this->productRepository = $productRepository;
    }


    /**
     * @param ProductInterface $product
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createProduct(ProductInterface $product):bool
    {
        $productParams = $this->transformProductIntoParam($product, Request::HTTP_METHOD_POST);
        return $this->actionOnProduct($productParams, Request::HTTP_METHOD_POST);
    }

    /**
     * @param ProductInterface $product
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateProduct(ProductInterface $product):bool
    {
        $productParams = $this->transformProductIntoParam($product, Request::HTTP_METHOD_PUT);
        return $this->actionOnProduct($productParams, Request::HTTP_METHOD_PUT);
    }

    /**
     * @param ProductInterface $product
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteProduct(ProductInterface $product):bool
    {
        $productParams = $this->transformProductIntoParam($product, Request::HTTP_METHOD_DELETE);
        return $this->actionOnProduct($productParams, Request::HTTP_METHOD_DELETE);
    }

    /**
     * @param ProductInterface $product
     * @param string $method
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function actionOnProduct(array $productParams, string $method):bool
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

        if(($status != "200") && isset($responseArray->message)) {
            ////LOGGER////
            $this->apiService->getLogger()->debug('Response : status => ' . $status . ' message : ' . $responseArray['message']);
        }

        return $status == "200";
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
            'sku' =>  $this->config->getTransiteoProductSku($product),
            'type' => 'SKU',
            'value' => $product->getName() ?? '',
            'weight' => $product->getWeight() ?? 0.5,
            'weight_unit' => $this->config->getWeightUnit($product->getStoreId()),
            'store_id' => $product->getStoreId()
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
     * @param array|null $storeIds If = null, all stores values will be affected
     */
    public function asyncCreateMultipleStoreValuesOfProduct(int $productId, ?array $storeIds = null):void
    {
        try{
            foreach ($this->getStoreValuesOfProduct($productId, $storeIds) as $product){
                $this->asyncCreateProduct($product);
            }
        }catch(\Magento\Framework\Exception\NoSuchEntityException $e){
            $this->apiService->getLogger()->error($e);
        }
    }

    /**
     * @param int $productId
     * @param array|null $storeIds If = null, all stores values will be affected
     */
    public function asyncDeleteMultipleStoreValuesOfProduct(int $productId,?array $storeIds = null):void
    {
        try{
            foreach ($this->getStoreValuesOfProduct($productId, $storeIds) as $product){
                $this->asyncDeleteProduct($product);
            }
        }catch(\Magento\Framework\Exception\NoSuchEntityException $e){
            $this->apiService->getLogger()->error($e);
        }
    }

    /**
     * @param int $productId
     * @param array|null $storeIds If = null, all stores values will be affected
     */
    public function asyncUpdateMultipleStoreValuesOfProduct(int $productId,?array $storeIds = null):void
    {
        try{
            foreach ($this->getStoreValuesOfProduct($productId, $storeIds) as $product){
                $this->asyncUpdateProduct($product);
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
            $storeIds = array_keys($this->storeManager->getStores(true));
        }
        $products = [];
        foreach ($storeIds as $storeId){
            $products[] = $this->productRepository->getById($productId, false, $storeId);
        }
        return $products;
    }



}
