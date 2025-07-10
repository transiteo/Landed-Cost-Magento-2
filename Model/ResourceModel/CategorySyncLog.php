<?php
    namespace Transiteo\LandedCost\Model\ResourceModel;

    use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

    class CategorySyncLog extends AbstractDb
    {
        protected function _construct(): void
        {
            $this->_init('transiteo_category_sync_log', 'log_id');
        }
    }

