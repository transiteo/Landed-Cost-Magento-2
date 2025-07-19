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

    namespace Transiteo\LandedCost\Observer\Sync\Category;

    use Exception;
    use Magento\Catalog\Api\Data\CategoryInterface;
    use Magento\Framework\Event\Observer;
    use Magento\Framework\Event\ObserverInterface;
    use Psr\Log\LoggerInterface;
    use Transiteo\LandedCost\Model\CountryExtractor;
    use Transiteo\LandedCost\Service\CategorySync;

    class StoreViewAdd implements ObserverInterface
    {
        /**
         * @param CategorySync $categorySync
         * @param CountryExtractor $countryExtractor
         * @param LoggerInterface $logger
         */
        public function __construct(
            private CategorySync     $categorySync,
            private CountryExtractor $countryExtractor,
            private LoggerInterface $logger
        ) {
        }

        public function execute(Observer $observer)
        {
            /**
             * @var CategoryInterface $product
             */
            try {
                $store = $observer->getEvent()->getStore();

                if (empty($countryCode = $this->countryExtractor->getCountryByStoreCode($store->getCode()))) {
                    return;
                }

                $this->categorySync->addCategoriesToAsync([], $countryCode);
            } catch (Exception $e) {
                $this->logger->error($e);
            }
        }
    }
