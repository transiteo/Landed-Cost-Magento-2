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

    namespace Transiteo\LandedCost\Service;

    use Exception;
    use Magento\Customer\Model\CustomerFactory;
    use Magento\Customer\Model\ResourceModel\Customer;
    use Magento\Framework\Exception\LocalizedException;
    use Magento\Framework\Exception\NoSuchEntityException;
    use Magento\Framework\MessageQueue\PublisherInterface;
    use Magento\Framework\Webapi\Rest\Request;
    use Transiteo\LandedCost\Model\TransiteoApiService;
    use Webkul\Marketplace\Model\Seller;
    use Webkul\Marketplace\Model\SellerFactory;
    use Webkul\Marketplace\Model\ResourceModel\Seller as SellerResource;
    use function json_decode;

    class SellerSync
    {
        public const SYNC_SELLER_TOPIC = "transiteo.sync.seller";

        /**
         * @param TransiteoApiService $apiService
         * @param PublisherInterface $publisher
         * @param Customer $customerResource
         * @param CustomerFactory $customerFactory
         * @param SellerFactory $sellerFactory
         * @param SellerResource $sellerResource
         */
        public function __construct(
            private TransiteoApiService $apiService,
            private PublisherInterface  $publisher,
            private Customer $customerResource,
            private CustomerFactory $customerFactory,
            private SellerFactory $sellerFactory,
            private SellerResource $sellerResource
        ) {
        }

        /**
         * Send seller information to Transiteo.
         *
         * @param int $sellerID
         *
         * @return string|null
         */
        public function sendSeller(int $sellerID): ?string
        {
            // load seller
            if (empty($sellerID)) {
                return null;
            }

            $seller = $this->sellerFactory->create();

            try {
                $this->sellerResource->load($seller, $sellerID);
            } catch (Exception) {
                return null;
            }

            if (!$seller->getIsSeller()) {
                return null;
            }

            $sellerParams = $this->transformSellerIntoParam($seller);

            return $this->actionOnSeller($sellerParams);
        }

        /**
         * @param Seller $seller
         *
         * @return array
         * @throws LocalizedException
         */
        public function transformSellerIntoParam(Seller $seller): array
        {
            $customer = $this->customerFactory->create();
            try {
                $this->customerResource->load($customer, $seller->getSellerId());
            } Catch (Exception) {
            }

            $result = [
                'id' => $seller->getSellerId(),
                'name' => $seller->getShopTitle(),
                'mail' => $customer->getEmail(),
                'manager_name' => $customer->getName(),
                'phone' => $seller->getContactNumber(),
                'vat_number' => $customer->getTaxvat(),
                //'eori_number' => ?
                'country' => $seller->getCountryPic(),
                "city" => $seller->getCompanyLocality(),
            ];

            /*
              "street": "11 rue de la République",
              "postal_code": "75002",
              "SYDEREP": {
                        "CRITEO": 8765RFGH76T,
                  "REFASHION": HJGFRTYHUY6T5*/

            $result = array_filter($result, function ($value) {
                return !empty($value);
            });

            return $result;
        }

        /**
         * @param $seller
         */
        public function createSellerAsync($seller): void
        {
            $data = [
                'seller_id' => $seller->getId(),
                'action' => 'create'
            ];

            $message = serialize($data);
            $this->publisher->publish(self::SYNC_SELLER_TOPIC, $message);
        }

        /**
         * Create seller
         *
         * @param array $sellerParams
         * @return string|null Return error message or null if success.
         *
         * @throws NoSuchEntityException
         */
        public function actionOnSeller(array $sellerParams): ?string
        {
            $request = [
                'headers' => [
                    'Content-type' => 'application/json',
                    'Authorization' => $this->apiService->getIdToken()
                ],
                'json' => ['sellers' => [$sellerParams]]
            ];

            $url = TransiteoApiService::API_REQUEST_URI . "v1/customer/sellers";

            $response = $this->apiService->doRequest(
                $url,
                $request,
                Request::HTTP_METHOD_POST
            );

            $status = $response->getStatusCode();

            $responseBody = $response->getBody();
            $responseContent = $responseBody->getContents();

            $responseArray = json_decode($responseContent, true);

            if (($status == "401") && isset($responseArray['message']) && $responseArray['message'] === "The incoming token has expired") {
                $this->apiService->refreshIdToken();

                return $this->actionOnSeller($sellerParams);
            }

            if ($status != "200") {
                if (isset($responseArray['message'])) {
                    ////LOGGER////
                    $this->apiService->getLogger()->debug('Response : status => ' . $status . ' message : ' . $responseArray['message']);
                    return $responseArray['message'];
                }
                if ($response->getReasonPhrase()) {
                    return $response->getReasonPhrase();
                }
            }

            return null;
        }
    }
