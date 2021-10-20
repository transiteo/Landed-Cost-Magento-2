<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\LandedCost\Service;


use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\Store\Model\StoreManagerInterface;
use Transiteo\LandedCost\Model\Config;
use Transiteo\LandedCost\Model\TransiteoApiService;

class OrderSync
{

    public const SYNC_ORDER_TOPIC = "transiteo.sync.order";

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
        PublisherInterface $publisher
    )
    {
        $this->config = $config;
        $this->apiService = $apiService;
        $this->storeManager = $storeManager;
        $this->publisher = $publisher;
    }


    /**
     * @param OrderInterface $order
     * @return string|null Return error message or null if success.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createOrder(OrderInterface $order):?string
    {
        $orderParams = $this->transformOrderIntoParam($order, Request::HTTP_METHOD_POST);
        return $this->actionOnOrder($orderParams, Request::HTTP_METHOD_POST);
    }

    /**
     * @param OrderInterface $order
     * @return string|null Return error message or null if success.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateOrder(OrderInterface $order):?string
    {
        $orderParams = $this->transformOrderIntoParam($order, Request::HTTP_METHOD_PUT);
        return $this->actionOnOrder($orderParams, Request::HTTP_METHOD_PUT);
    }

    /**
     * @param OrderInterface $order
     * @return string|null Return error message or null if success.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteOrder(OrderInterface $order):?string
    {
        $orderParams = $this->transformOrderIntoParam($order, Request::HTTP_METHOD_DELETE);
        return $this->actionOnOrder($orderParams, Request::HTTP_METHOD_DELETE);
    }

    /**
     * @param array $orderParams
     * @param string $method
     * @return string|null Return error message or null if success.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function actionOnOrder(array $orderParams, string $method):?string
    {
        $request = [
            'headers' => [
                'Content-type'     => 'application/json',
                'Authorization' => $this->apiService->getIdToken()
            ],
            'json' => $orderParams
        ];

        $url = TransiteoApiService::API_REQUEST_URI . "v1/customer/orders";
        if($method !== Request::HTTP_METHOD_POST){
            $url .= '/' . $orderParams['order_id'];
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
            return $this->actionOnOrder($orderParams, $method);
        }

        if(($status != "200") && isset($responseArray->message)) {
            ////LOGGER////
            $this->apiService->getLogger()->debug('Response : status => ' . $status . ' message : ' . $responseArray['message']);
            return $responseArray['message'];
        }

        return null;
    }

    /**
     * @param OrderInterface $order
     * @param string $method
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function transformOrderIntoParam(OrderInterface $order, string $method):array
    {
        //send only the order id for deleting
        if($method === Request::HTTP_METHOD_DELETE){
            return [
                'order_id' => $order->getIncrementId()
            ];
        }

        //send all other informations to save
        $products = [];
        $currencyCode = $order->getStoreCurrencyCode();
        $productTotal = 0;
        foreach ($order->getItems() as $item){
            if ($item->getParentItem()) {
                continue;
            }
            $qty = $item->getQtyOrdered();
            $rowTotal = $item->getRowTotal();
            $price = $rowTotal / $qty;
            $productTotal += $rowTotal;
            $products[] = [
                'sku' => $this->config->getTransiteoProductSku($item),
                "quantity" => $qty,
                "unit_price" => $price,
                "unit_price_currency" => $currencyCode,
            ];
        }
        $statusHistories = $order->getStatusHistories();
        if(empty($statusHistories)){
            $orderUpdateDate = strtotime($order->getCreatedAt());
        }else{
            $orderUpdateDate = strtotime(end($statusHistories)->getCreatedAt());
        }
        $result = [
            'order_id' => $order->getData($this->config->getOrderIdentifier()),
            'url' => $this->storeManager->getStore($order->getStoreId())->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB),
            'order_date_hour' => (int) (strtotime($order->getCreatedAt()) . '000'),
            'customer_firstname' => $order->getCustomerFirstname(),
            'customer_lastname' => $order->getCustomerLastname(),
            'departure_country' => $this->config->getIso3Country($this->config->getWebsiteCountry()),
            'arrival_country' => $this->config->getIso3Country($order->getShippingAddress()->getCountryId()),
            'products' => $products,
            "shipping_carrier" => $this->getShippingCarrier($order),
            "amount_products" => (float) $productTotal,
            "amount_shipping" => (float) $order->getShippingAmount(),
            "amount_duty" => (float) $order->getTransiteoDuty(),
            "amount_vat" => (float) $order->getTransiteoVat(),
            "amount_specialtaxes" => (float) $order->getTransiteoSpecialTaxes(),
            "currency" => $currencyCode,
            "order_statut" => $this->transformStatusIntoTransiteoOne($order->getStatus()),
            "order_update_statut" => (int) ($orderUpdateDate . '000')
        ];



        return $result;
    }


    /**
     * @param $order
     * @return string
     */
    protected function getShippingCarrier($order): string
    {
        /**
         * @todo Warning, only the last carrier track of latests shipments
         */
        /**
         * @var Shipment $shipment
         */
        $shipments = $order->getShipmentsCollection()->getItems();
        if(is_array($shipments)){
            $shipments = array_reverse($shipments);
        }
        foreach($shipments as $shipment){
            $tracks = $shipment->getTracks();
            if(is_array($tracks) && !empty($tracks)){
                $track = end($tracks);
                break;
            }
        }
        if(isset($track)){
            return $track->getCarrierCode();
        }

        return  $order->getShippingDescription();
    }

    /**
     * @param string $orderStatus
     * @return string
     */
    protected function transformStatusIntoTransiteoOne(string $orderStatus):string
    {
        $correspondence = $this->config->getStatusCorrespondences();
        if(array_key_exists($orderStatus, $correspondence)){
            return $correspondence[$orderStatus];
        }

        return Config::TRANSITEO_DEFAULT_STATUS;
    }


    /**
     * @param OrderInterface $order
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function asyncCreateOrder(OrderInterface $order): void
    {
        $this->asyncActionOnOrder($order, Request::HTTP_METHOD_POST);
    }

    /**
     * @param OrderInterface $order
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function asyncUpdateOrder(OrderInterface $order): void
    {
        $this->asyncActionOnOrder($order, Request::HTTP_METHOD_PUT);
    }

    /**
     * @param OrderInterface $order
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function asyncDeleteOrder(OrderInterface $order): void
    {
        $this->asyncActionOnOrder($order, Request::HTTP_METHOD_DELETE);
    }

    /**
     * @param OrderInterface $order
     * @param string $method
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function asyncActionOnOrder(OrderInterface $order, string $method): void
    {
        $data = [
            'method' => $method
        ];
        try{
            $data['order'] = $this->transformOrderIntoParam($order, $method);
        }catch(\Exception $e){
            $this->apiService->getLogger()->error($e);
            $data['order_id'] = $order->getEntityId();
        }

        $message = serialize($data);
        $this->publisher->publish(self::SYNC_ORDER_TOPIC, $message);
    }

}
