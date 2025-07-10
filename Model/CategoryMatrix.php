<?php
    namespace Transiteo\LandedCost\Model;

    use Magento\Framework\Model\AbstractModel;

    class CategoryMatrix extends AbstractModel
    {
        /** @var string */
        protected $_eventPrefix = 'transiteo_category_matrix';

        protected function _construct(): void
        {
            $this->_init(ResourceModel\CategoryMatrix::class);
        }
    }
