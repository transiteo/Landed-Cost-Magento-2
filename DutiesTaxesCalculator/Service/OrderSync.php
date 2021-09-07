<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\DutiesTaxesCalculator\Service;


use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Transiteo\DutiesTaxesCalculator\Model\TransiteoApiService;

class OrderSync
{

    /**
     * @var TransiteoApiService
     */
    protected $apiService;
    /**
     * @var \Transiteo\DutiesTaxesCalculator\Model\Config
     */
    protected $config;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @param TransiteoApiService $apiService
     * @param \Transiteo\DutiesTaxesCalculator\Model\Config $config
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        TransiteoApiService $apiService,
        \Transiteo\DutiesTaxesCalculator\Model\Config $config,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    )
    {
        $this->config = $config;
        $this->apiService = $apiService;
        $this->storeManager = $storeManager;
        $this->dateTime = $dateTime;
    }


    /**
     * @param OrderInterface $order
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createOrder(OrderInterface $order):bool
    {
        return $this->actionOnOrder($order, Request::HTTP_METHOD_POST);
    }

    /**
     * @param OrderInterface $order
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateOrder(OrderInterface $order):bool
    {
        return $this->actionOnOrder($order, Request::HTTP_METHOD_PUT);
    }

    /**
     * @param OrderInterface $order
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteOrder(OrderInterface $order):bool
    {
        return $this->actionOnOrder($order, Request::HTTP_METHOD_DELETE);
    }

    /**
     * @param OrderInterface $order
     * @param string $method
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function actionOnOrder(OrderInterface $order, string $method):bool
    {
        $request = [
            'headers' => [
                'Content-type'     => 'application/json',
                'Authorization' => $this->apiService->getIdToken()
            ],
            'json' => $this->transformOrderIntoParam($order, $method)
        ];

        $url = TransiteoApiService::API_REQUEST_URI . "v1/customer/orders";
        if($method !== Request::HTTP_METHOD_POST){
            $url .= '/' . $order->getIncrementId();
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


        if ($status == "401") {
            if (isset($responseArray->message) && $responseArray->message == "The incoming token has expired") {
                $this->apiService->refreshIdToken();
                return $this->createOrder($order);
            }
        }

        if(($status != "200") && isset($responseArray->message)) {
            ////LOGGER////
            $this->apiService->getLogger()->debug('Response : status => ' . $status . ' message : ' . $responseArray['message']);
        }

        return $status == "200";
    }

    /**
     * @param OrderInterface $order
     * @param string $method
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function transformOrderIntoParam(OrderInterface $order, string $method):array
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
                'sku' => $item->getData($this->config->getProductIdentifier()),
                "quantity" => $qty,
                "unit_price" => $price,
                "unit_currrency_price" => $currencyCode,
            ];
        }
        $statusHistories = $order->getStatusHistories();
        $result = [
            'order_id' => $order->getIncrementId(),
            'url' => $this->storeManager->getStore($order->getStoreId())->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB),
            'order_date_hour' => $this->dateTime->gmtTimestamp($order->getCreatedAt()),
            'customer_firstname' => $order->getCustomerFirstname(),
            'customer_lastname' => $order->getCustomerLastname(),
            'departure_country' => $this->config->getIso3Country($this->config->getWebsiteCountry()),
            'arrival_country' => $this->config->getIso3Country($order->getShippingAddress()->getCountryId()),
            'products' => $products,
            "shipping_carrier" => $order->getShippingDescription(),
            "amount_products" => (float) $productTotal,
            "amount_shipping" => (float) $order->getShippingAmount(),
            "amount_duty" => (float) $order->getTransiteoDuty(),
            "amount_vat" => (float) $order->getTransiteoVat(),
            "amount_specialtaxes" => (float) $order->getTransiteoSpecialTaxes(),
            "currency" => $currencyCode,
            "order_statut" => $order->getStatus(),
            "order_update_statut" => end($statusHistories)->getCreatedAt()
        ];

        return $result;
    }

}
