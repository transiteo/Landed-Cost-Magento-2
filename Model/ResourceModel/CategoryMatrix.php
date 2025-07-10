<?php

    namespace Transiteo\LandedCost\Model\ResourceModel;

    use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

    class CategoryMatrix extends AbstractDb
    {
        protected function _construct(): void
        {
            $this->_init('transiteo_category_matrix', 'entity_id');
        }
    }
