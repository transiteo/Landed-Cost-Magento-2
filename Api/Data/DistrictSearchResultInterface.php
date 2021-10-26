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

namespace Transiteo\LandedCost\Api\Data;

interface DistrictSearchResultInterface
{
    /**
     * Get districts
     *
     * @return DistrictInterface[]
     */
    public function getItems();

    /**
     * Set districts
     *
     * @param DistrictInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
