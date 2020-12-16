<?php

namespace Transiteo\Taxes\Model\Config\Source;

use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\ScopeInterface;
use Transiteo\Taxes\Api\Data\DistrictInterface;
use Transiteo\Taxes\Model\DistrictRepository;

class District implements OptionSourceInterface
{

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var DistrictRepository
     */
    protected $districtRepository;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * District constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DistrictRepository $districtRepository
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DistrictRepository $districtRepository,
        CountryFactory $countryFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->districtRepository    = $districtRepository;
        $this->scopeConfig = $scopeConfig;
    }

    public function toOptionArray()
    {
        $country_id = $this->scopeConfig->getValue(
            'general/country/default',
            ScopeInterface::SCOPE_WEBSITE
        );

        //[238, 41, 32]
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(DistrictInterface::COUNTRY, ["US", "BR", "CA"], "in")
            ->create();

        $searchResult = $this->districtRepository->getList($searchCriteria);

        $items = $searchResult->getItems();

        $results = [];
        $results[] = [ 'value' => "", 'label' => "NOT CONCERNED"];

        foreach ($items as $item) {
            $results[]= [ 'value' => $item->getIso(), 'label' => $item->getLabel()];
        }

        return $results;
    }
}
