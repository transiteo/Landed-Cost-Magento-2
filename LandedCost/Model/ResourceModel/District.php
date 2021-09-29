<?php
/*
 * @copyright Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Transiteo\LandedCost\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Transiteo\LandedCost\Api\Data\DistrictInterface;

class District extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('transiteo_district', DistrictInterface::ID);
    }
}
