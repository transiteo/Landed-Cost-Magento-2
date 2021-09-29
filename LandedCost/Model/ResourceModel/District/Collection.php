<?php
/*
 * @copyright Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Transiteo\LandedCost\Model\ResourceModel\District;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Transiteo\LandedCost\Api\Data\DistrictInterface;
use Transiteo\LandedCost\Model;

class Collection extends AbstractCollection
{
    /**
     * @inheritDoc
     */
    protected $_idFieldName = DistrictInterface::ID;

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_init(
            Model\District::class,
            Model\ResourceModel\District::class
        );
    }
}
