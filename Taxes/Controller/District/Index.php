<?php

declare(strict_types=1);

namespace Transiteo\Taxes\Controller\District;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Transiteo\Taxes\Api\Data\DistrictInterface;
use Transiteo\Taxes\Model\DistrictRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Index extends Action
{
    protected $searchCriteriaBuilder;

    protected $districtRepository;

    public function __construct(
        Context $context,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DistrictRepository $districtRepository
    ) {
        parent::__construct($context);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->districtRepository    = $districtRepository;
    }

    public function execute()
    {   

        $country_id = $this->getRequest()->getParam('country');

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(DistrictInterface::COUNTRY, $country_id)
            ->create();

        $searchResult = $this->districtRepository->getList($searchCriteria);

        /** @var Json $jsonResult */
        $jsonResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $items = [];
        foreach ($searchResult->getItems() as $item) {
            $items[] = $item->getData();
        }

        $jsonResult->setData([
            'items' => $items
        ]);

        return $jsonResult;
    }

}
