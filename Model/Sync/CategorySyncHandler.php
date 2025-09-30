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
    use Magento\Framework\Exception\NoSuchEntityException;
    use Transiteo\LandedCost\Logger\QueueLogger;
    use Transiteo\LandedCost\Service\CategorySync;

    /**
     *
     */
    class CategorySyncHandler
    {
        /**
         * @param QueueLogger $logger
         * @param CategorySync $categorySync
         */
        public function __construct(
            protected QueueLogger  $logger,
            protected CategorySync $categorySync,
            protected \Transiteo\LandedCost\Model\Config $config
        )
        {}

        /**
         * @param string $message
         * @throws NoSuchEntityException|Exception
         */
        public function process(string $message)
        {
            if (!$this->config->isSyncCategoryEnabled()) {
                return;
            }
            try {
                $this->logger->debug("CategorySyncHandler: " . $message);
                $params = unserialize($message);
                if (array_key_exists("category_ids", $params)) {
                    $this->categorySync->actionOnCategories($params['category_ids'], 1, $params['country'] ?? null);
                } elseif (array_key_exists("country", $params)) {
                    $this->categorySync->getListOfCategories($params['country']);
                }
            } catch (Exception $exception) {
                $this->logger->error($exception);
                throw $exception;
            }
        }
    }
