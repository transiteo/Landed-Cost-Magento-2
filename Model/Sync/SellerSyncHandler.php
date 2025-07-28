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
    use Magento\TestFramework\Integrity\Library\PhpParser\Throws;
    use Transiteo\LandedCost\Logger\QueueLogger;
    use Transiteo\LandedCost\Service\SellerSync;

    /**
     *
     */
    class SellerSyncHandler
    {
        /**
         * @param QueueLogger $logger
         * @param SellerSync $sellerSync
         */
        public function __construct(
            private QueueLogger  $logger,
            private SellerSync $sellerSync,
        )
        {}

        /**
         * Consumes queue message to process seller synchronization.
         *
         * @param string $message
         *
         * @throws NoSuchEntityException|Exception
         */
        public function process(string $message): void
        {
            try {
                $params = unserialize($message);
                $sellerID = intval($params['seller_id']);
                $action = $params['action'];

                if (empty($sellerID) || empty($action)) {
                    return;
                }

                switch ($action) {
                    case 'create':
                        $this->sellerSync->sendSeller($sellerID);
                        break;
                    default:
                        throw new Exception('Unknown action: ' . $action);
                }
            } catch (Exception $exception) {
                $this->logger->error($exception);
                throw $exception;
            }
        }
    }
