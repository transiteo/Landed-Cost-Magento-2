<?php
    namespace Transiteo\LandedCost\Model;

    use Magento\Framework\Model\AbstractModel;

    class CategorySyncLog extends AbstractModel
    {
        protected $_eventPrefix = 'transiteo_category_sync_log';

        public const TYPE_POST = 'post';
        public const TYPE_GET = 'get';

        protected function _construct(): void
        {
            $this->_init(ResourceModel\CategorySyncLog::class);
        }
    }
