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

    use Blackbird\ContentManager\Block\View\Field\Product;
    use Magento\Catalog\Api\Data\CategoryInterface;
    use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
    use Magento\Framework\Exception\NoSuchEntityException;
    use Magento\Framework\MessageQueue\PublisherInterface;
    use Magento\Framework\Webapi\Rest\Request;
    use Transiteo\LandedCost\Model\CategoryMatrixFactory;
    use Transiteo\LandedCost\Model\CategorySyncLog;
    use Transiteo\LandedCost\Model\CategorySyncLogFactory;
    use Transiteo\LandedCost\Model\CountryExtractor;
    use Transiteo\LandedCost\Model\TransiteoApiService;
    use Transiteo\LandedCost\Model\ResourceModel\CategoryMatrix as CategoryMatrixResourceModel;
    use Transiteo\LandedCost\Model\ResourceModel\CategoryMatrix\CollectionFactory as CategoryMatrixCollectionFactory;
    use Transiteo\LandedCost\Model\ResourceModel\CategorySyncLog as CategorySyncLogResourceModel;
    use function json_decode;

    class CategorySync
    {
        public const SYNC_CATEGORY_TOPIC = "transiteo.sync.category";

        public const POST_CATEGORY_PAGE_SIZE = 100;

        /**
         * @param TransiteoApiService $apiService
         * @param CountryExtractor $countryExtractor
         * @param CollectionFactory $categoryCollectionFactory
         * @param CategoryMatrixCollectionFactory $categoryMatrixCollectionFactory
         * @param CategoryMatrixResourceModel $categoryMatrixResourceModel
         * @param PublisherInterface $publisher
         */
        public function __construct(
            private TransiteoApiService $apiService,
            private CountryExtractor    $countryExtractor,
            private CollectionFactory   $categoryCollectionFactory,
            private CategoryMatrixCollectionFactory $categoryMatrixCollectionFactory,
            private CategoryMatrixFactory $categoryMatrixFactory,
            private CategorySyncLogFactory $categorySyncLogFactory,
            private CategoryMatrixResourceModel $categoryMatrixResourceModel,
            private CategorySyncLogResourceModel $categorySyncLogResourceModel,
            private PublisherInterface  $publisher,
        )
        {
        }

        /**
         * @param string|null $codePays
         * @param array $categoryIds
         *
         * @return array|null
         *
         * @throws NoSuchEntityException
         */
        public function getListOfCategories(?string $codePays = null, array $categoryIds = []): ?array
        {
            $request = [
                'headers' => [
                    'Content-type' => 'application/json',
                    'Authorization' => $this->apiService->getIdToken()
                ]
            ];

            $url = TransiteoApiService::API_REQUEST_URI . "v2/customer/categories?";

            if (empty($categoryIds)) {
                $categoryIds = 'all';
            } else {
                $categoryIds = implode(',', $categoryIds);
            }

            $url .= 'category_ids=' . $categoryIds;

            if (!empty($codePays)) {
                $url .= '&country_iso=' . $codePays;
            }

            // Enregistre la synchro de données
            $categorySyncLog = $this->categorySyncLogFactory->create()
                ->setAction(CategorySyncLog::TYPE_GET)
                ->setPayload($url)
                ->setCreatedAt(date('Y-m-d H:i:s'));
            $this->categorySyncLogResourceModel->save($categorySyncLog);

            $response = $this->apiService->doRequest(
                $url,
                $request
            );

            $status = $response->getStatusCode();

            $responseBody = $response->getBody();
            $responseContent = $responseBody->getContents();

            $responseArray = json_decode($responseContent, true);

            if (($status == "401") && isset($responseArray['message']) && $responseArray['message'] === "The incoming token has expired") {
                $this->apiService->refreshIdToken();
                return $this->getListOfCategories($codePays, $categoryIds);
            }

            if ($status != "200") {
                if (isset($responseArray['message'])) {
                    ////LOGGER////
                    $this->apiService->getLogger()->debug('Response : status => ' . $status . ' message : ' . $responseArray['message']);
                    return null;
                }
                if ($response->getReasonPhrase()) {
                    return null;
                }
            }

            foreach ($responseArray['data'] as $taxeData) {
                // Enregistrement des données dans transiteo_category_matrix
                $this->saveCategoryTaxData($taxeData);
            }

            return $responseArray;
        }

        /**
         * @param CategoryInterface $category
         *
         * @return string|null
         *
         * @throws NoSuchEntityException
         */
        public function createCategory(CategoryInterface $category): ?string
        {
            $categoryParams = $this->transformCategoryIntoParam($category);

            return $this->actionOnCategory($categoryParams);
        }

        /**
         * @param CategoryInterface $category
         *
         * @return array
         */
        public function transformCategoryIntoParam(CategoryInterface $category): array
        {
            $result = [];

            $result[] = [
                'category_name' => $category->getName(),
                'category_id' => $category->getId(),
                'country_iso' => $this->countryExtractor->getCountries()
            ];

            return $result;
        }

        /**
         * @param array $categoryIds
         */
        public function addCategoriesToAsync(array $categoryIds = []): void
        {
            $data = [
                'category_ids' => $categoryIds
            ];

            $message = serialize($data);
            $this->publisher->publish(self::SYNC_CATEGORY_TOPIC, $message);
        }

        /**
         * @param array $categoryIds
         *
         * @return bool indique si on a encore des lignes à traiter
         *
         * @throws \Magento\Framework\Exception\LocalizedException
         */
        public function actionOnCategories(array $categoryIds = [], int $page = 1): bool
        {
            $categories = $this->categoryCollectionFactory->create()
                ->addAttributeToSelect('name');

            if (!empty($categoryIds)) {
                $categories->addFieldToFilter('entity_id', ['in' => $categoryIds]);
            }

            $categories
                ->addFieldToFilter('level', ['gt' => 1])
                ->setPageSize(self::POST_CATEGORY_PAGE_SIZE)
                ->setCurPage($page);

            $categories->load();

            $hasNextPage = $categories->getSize() === self::POST_CATEGORY_PAGE_SIZE;

            $params = [];
            foreach ($categories as $category) {
                $categoryParams = $this->transformCategoryIntoParam($category);
                $params = array_merge($params, $categoryParams);
            }

            if (empty($params)) {
                return false;
            }

            $this->actionOnCategory($params);

            if ($hasNextPage) {
                $page += 1;
                return $this->actionOnCategories($categories->getAllIds(), $page);
            }

            return $hasNextPage;
        }

        /**
         * @param array $categoryParams
         * @param string $method
         *
         * @return string|null Return error message or null if success.
         *
         * @throws NoSuchEntityException
         */
        public function actionOnCategory(array $categoryParams): ?string
        {
            $request = [
                'headers' => [
                    'Content-type' => 'application/json',
                    'Authorization' => $this->apiService->getIdToken()
                ],
                'json' => ['categories' => $categoryParams]
            ];

            $url = TransiteoApiService::API_REQUEST_URI . "v2/customer/categories";

            // Enregistre la synchro de données
            $categorySyncLog = $this->categorySyncLogFactory->create()
                ->setAction(CategorySyncLog::TYPE_POST)
                ->setPayload(json_encode($categoryParams))
                ->setCreatedAt(date('Y-m-d H:i:s'));
            $this->categorySyncLogResourceModel->save($categorySyncLog);

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

                return $this->actionOnCategory($categoryParams);
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

            foreach ($responseArray as $taxeData) {
                // Enregistrement des données dans transiteo_category_matrix
                $this->saveCategoryTaxData($taxeData);
            }

            return null;
        }

        /**
         * Mise à jours de la catégorie matrix.
         *
         * @param array $taxeData
         *
         * @return void
         *
         * @throws \Magento\Framework\Exception\AlreadyExistsException
         */
        private function saveCategoryTaxData(array $taxeData): void
        {
            if (!empty($taxeData['category_id']) && !empty($taxeData['country'])) {
                // récupération de la ligne avec la catégorie et le pays
                $categoryMatrix = $this->categoryMatrixCollectionFactory->create()
                    ->addFieldToFilter('category_id', $taxeData['category_id'])
                    ->addFieldToFilter('country_iso', $taxeData['country'])
                    ->getFirstItem();

                if (empty($categoryMatrix) || !$categoryMatrix->getId()) {
                    $categoryMatrix = $this->categoryMatrixFactory->create();
                }

                $categoryMatrix
                    ->setCategoryId($taxeData['category_id'])
                    ->setCountryIso($taxeData['country'])
                    ->setRestricted($taxeData['restriction_status'] === 'authorized' ? 1 : 0)
                    ->setDuty($taxeData['duty'] ?? 0)
                    ->setLocalTax($taxeData['local_tax'] ?? 0)
                    ->setSalesTax($taxeData['salestax'] ?? 0)
                    ->setSpecialTaxes(
                        isset($taxeData['special_tax']) ? json_encode($taxeData['special_tax']) : null
                    )
                    ->setEcoTax($taxeData['eco_tax'] ?? 0)
                    ->setUpdatedAt(date('Y-m-d H:i:s'));

                $this->categoryMatrixResourceModel->save($categoryMatrix);
            }
        }
    }
