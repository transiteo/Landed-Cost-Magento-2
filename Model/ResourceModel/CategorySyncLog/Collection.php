<?php

    namespace Transiteo\LandedCost\Model\ResourceModel\CategorySyncLog;

    use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
    use Transiteo\LandedCost\Model\CategorySyncLog as Model;
    use Transiteo\LandedCost\Model\ResourceModel\CategorySyncLog as ResourceModel;

    class Collection extends AbstractCollection
    {
        protected function _construct(): void
        {
            $this->_init(Model::class, ResourceModel::class);
        }
    }

