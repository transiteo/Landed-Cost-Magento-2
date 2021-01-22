<?php
/*
 * @copyright Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Transiteo\CrossBorder\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Transiteo\CrossBorder\Api\Data;
use Transiteo\CrossBorder\Api\DistrictRepositoryInterface;
use Transiteo\CrossBorder\Model\ResourceModel\District\Collection;
use Transiteo\CrossBorder\Model\ResourceModel\District\CollectionFactory as DistrictCollectionFactory;

class DistrictRepository implements DistrictRepositoryInterface
{
    /**
     * @var ResourceModel\District
     */
    private $resourceDistrict;

    /**
     * @var Data\DistrictInterfaceFactory
     */
    private $districtFactory;

    /**
     * @var Data\DistrictSearchResultInterface
     */
    private $searchResultsFactory;

    /**
     * @var DistrictCollectionFactory
     */
    private $districtCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * DistrictRepository constructor.
     *
     * @param ResourceModel\District $resourceDistrict
     * @param Data\DistrictInterfaceFactory $districtFactory
     * @param Data\DistrictSearchResultInterface $searchResultsFactory
     * @param DistrictCollectionFactory $districtCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceModel\District $resourceDistrict,
        Data\DistrictInterfaceFactory $districtFactory,
        Data\DistrictSearchResultInterfaceFactory $searchResultsFactory,
        DistrictCollectionFactory $districtCollectionFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resourceDistrict          = $resourceDistrict;
        $this->districtFactory           = $districtFactory;
        $this->searchResultsFactory      = $searchResultsFactory;
        $this->districtCollectionFactory = $districtCollectionFactory;
        $this->collectionProcessor       = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(Data\DistrictInterface $district)
    {
        try {
            $this->resourceDistrict->save($district);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }

        return $district;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $districtId)
    {
        $district = $this->districtFactory->create();
        $this->resourceDistrict->load($district, $districtId);

        if (! $district->getId()) {
            throw new NoSuchEntityException(__('District with id "%1" does not exist', $districtId));
        }

        return $district;
    }

    /**
     * @inheritDoc
     */
    public function delete(Data\DistrictInterface $district)
    {
        try {
            $this->resourceDistrict->delete($district);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        }

        return $district;
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $districtId)
    {
        return $this->delete($this->getById($districtId));
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->districtCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $collection->load();

        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * Delete all Districts
     */
    public function deleteAllDistricts()
    {
        $collection = $this->districtCollectionFactory->create();
        $collection->getConnection()->delete($collection->getMainTable());
    }
}
