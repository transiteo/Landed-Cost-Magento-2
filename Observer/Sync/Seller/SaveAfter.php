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

    namespace Transiteo\LandedCost\Observer\Sync\Seller;

    use Exception;
    use Magento\Framework\Event\Observer;
    use Magento\Framework\Event\ObserverInterface;
    use Psr\Log\LoggerInterface;
    use Transiteo\LandedCost\Service\SellerSync;
    use Webkul\Marketplace\Model\Seller;

    class SaveAfter implements ObserverInterface
    {
        /**
         * @var LoggerInterface
         */
        protected $logger;

        /**
         * @param SellerSync $sellerSync
         * @param LoggerInterface $logger
         */
        public function __construct(
            protected SellerSync $sellerSync,
            LoggerInterface $logger,
            protected \Transiteo\LandedCost\Model\Config $config
        )
        {
            $this->logger = $logger;
        }

        /**
         * Add seller to update queue.
         *
         * @param Observer $observer
         *
         * @return void
         */
        public function execute(Observer $observer)
        {
            if (!$this->config->isEnabled()) {
                return;
            }
            try {
                /** @var Seller $seller */
                $seller = $observer->getEvent()->getObject();

                // Send seller only when shop_title is set
                $newShopTitle = trim((string)$seller->getData('shop_title'));
                $oldShopTitle = trim((string)$seller->getOrigData('shop_title'));

                if (empty($oldShopTitle) && !empty($newShopTitle)) {
                    $this->sellerSync->createSellerAsync($seller);
                }
            } catch (Exception $e) {
                $this->logger->error($e);
            }
        }
    }
