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

namespace Transiteo\LandedCost\Api;

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
