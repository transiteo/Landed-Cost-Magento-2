<?php
/*
 * @copyright Open Software License (OSL 3.0)
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
