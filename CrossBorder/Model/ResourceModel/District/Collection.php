<?php
/**
 * @author Joris HART <jhart@efaktory.fr>
 * @copyright Copyright (c) 2020 eFaktory (https://www.efaktory.fr)
 * @link https://www.efaktory.fr
 */
declare(strict_types=1);

namespace Transiteo\CrossBorder\Model\ResourceModel\District;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Transiteo\CrossBorder\Api\Data\DistrictInterface;
use Transiteo\CrossBorder\Model;

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
