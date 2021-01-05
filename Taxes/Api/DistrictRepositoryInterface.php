<?php
/**
 * @author Joris HART <jhart@efaktory.fr>
 * @copyright Copyright (c) 2020 eFaktory (https://www.efaktory.fr)
 * @link https://www.efaktory.fr
 */

namespace Transiteo\Taxes\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;

interface DistrictRepositoryInterface
{
    /**
     * @param Data\DistrictInterface $district
     * @return Data\DistrictInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\DistrictInterface $district);

    /**
     * @param int $districtId
     * @return Data\DistrictInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $districtId);

    /**
     * @param Data\DistrictInterface $district
     * @return Data\DistrictInterface
     * @throws CouldNotDeleteException
     */
    public function delete(Data\DistrictInterface $district);

    /**
     * @throws CouldNotDeleteException
     */
    public function deleteAllDistricts();

    /**
     * @param int $districtId
     * @return Data\DistrictInterface
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $districtId);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Data\DistrictSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
