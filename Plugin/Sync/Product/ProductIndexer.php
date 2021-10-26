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
namespace Transiteo\LandedCost\Plugin\Sync\Product;

use Magento\Catalog\Model\Indexer\Category\Product;
use Magento\MysqlMq\Model\ResourceModel\MessageStatusCollection;
use Transiteo\LandedCost\Service\ProductSync;

class ProductIndexer
{
    /**
     * @var ProductSync
     */
    protected $productSync;
    /**
     * @var MessageStatusCollection
     */
    protected $messageStatusCollection;

    /**
     * @param ProductSync $productSync
     * @param MessageStatusCollection $messageStatusCollection
     */
    public function __construct(
        ProductSync $productSync,
        MessageStatusCollection $messageStatusCollection
    )
    {
        $this->messageStatusCollection = $messageStatusCollection;
        $this->productSync = $productSync;
    }

    /**
     * @param Product $subject
     * @param $result
     */
    public function afterExecuteFull(Product $subject, $result)
    {
        $this->clearAllWaitingProductSyncMesssages();
        $this->productSync->asyncUpdateAllProducts();
    }

    /**
     * @param Product $subject
     * @param $result
     * @param $ids
     */
    public function afterExecute(Product $subject,$result, $ids){
        foreach ($ids as $id){
            $this->productSync->asyncUpdateMultipleStoreValuesOfProduct((int) $id);
        }
    }

    /**
     * @param Product $subject
     * @param $result
     * @param $ids
     */
    public function afterExecuteList(Product $subject,$result, $ids){
        foreach ($ids as $id){
            $this->productSync->asyncUpdateMultipleStoreValuesOfProduct((int) $id);
        }
    }

    /**
     * @param Product $subject
     * @param $result
     * @param $id
     */
    public function afterExecuteRow(Product $subject,$result, $id){
        $this->productSync->asyncUpdateMultipleStoreValuesOfProduct((int) $id);
    }

    /**
     *
     */
    protected function clearAllWaitingProductSyncMesssages():void
    {
        $connection =  $this->messageStatusCollection->getResource()->getConnection();
        $queueMessage = $connection->getTableName("queue_message");
        $queueMessageStatus = $connection->getTableName("queue_message_status");
        $sql = "DELETE qm FROM {$queueMessage} AS qm LEFT JOIN {$queueMessageStatus} qms on qm.id = qms.message_id WHERE qm.topic_name = 'transiteo.sync.product' AND qms.status = 2;";
        $connection->startSetup();
        $connection->query($sql);
        $connection->endSetup();
    }
}
