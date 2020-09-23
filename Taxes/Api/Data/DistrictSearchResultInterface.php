<?php
/**
 * @author Joris HART <jhart@efaktory.fr>
 * @copyright Copyright (c) 2020 eFaktory (https://www.efaktory.fr)
 * @link https://www.efaktory.fr
 */

namespace Transiteo\Taxes\Api\Data;

use Magento\Catalog\Api\Data\CategorySearchResultsInterface;

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
