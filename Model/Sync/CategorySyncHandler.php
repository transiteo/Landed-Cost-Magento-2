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

    use Exception;
    use Magento\Catalog\Api\ProductRepositoryInterface;
    use Magento\Framework\Exception\NoSuchEntityException;
    use Transiteo\LandedCost\Logger\QueueLogger;
    use Transiteo\LandedCost\Service\CategorySync;
    use Transiteo\LandedCost\Service\ProductSync;
    use function json_encode;

    /**
     *
     */
    class CategorySyncHandler
    {
        /**
         * @param QueueLogger $logger
         * @param ProductSync $productSync
         * @param ProductRepositoryInterface $productRepository
         */
        public function __construct(
            private QueueLogger  $logger,
            private CategorySync $categorySync
        )
        {}

        /**
         * @param string $message
         * @throws NoSuchEntityException|Exception
         */
        public function process(string $message)
        {
            try {
                $params = unserialize($message);
                if (array_key_exists("category_ids", $params)) {
                    $errorMessage = $this->categorySync->actionOnCategories($params['category_ids']);

                    if (!empty($errorMessage)) {
                        // log error
                        $requestParams = json_encode($params);

                        $message = "Error in response from Api, error : " . $errorMessage . "  in message " . $message . " with request : " . $requestParams;
                        $this->logger->debug($message);
                        throw new Exception($message);
                    }

                    return;
                }
            } catch (Exception $exception) {
                $this->logger->error($exception);
                throw $exception;
            }
        }
    }
