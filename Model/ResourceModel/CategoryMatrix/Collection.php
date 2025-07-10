<?php
    namespace Transiteo\LandedCost\Model\ResourceModel\CategoryMatrix;

    use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
    use Transiteo\LandedCost\Model\CategoryMatrix as Model;
    use Transiteo\LandedCost\Model\ResourceModel\CategoryMatrix as Resource;

    class Collection extends AbstractCollection
    {
        protected function _construct(): void
        {
            $this->_init(Model::class, Resource::class);
        }
    }
