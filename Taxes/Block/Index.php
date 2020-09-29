<?php

namespace Transiteo\Taxes\Block;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Transiteo\Taxes\Api\Data\DistrictInterface;
use Transiteo\Taxes\Controller\Cookie;
use Transiteo\Taxes\Model\DistrictRepository;

class Index extends \Magento\Framework\View\Element\Template
{
    protected $directoryBlock;
    protected $_isScopePrivate;
    protected $cookie;
    protected $scopeConfig;
    protected $districtRepository;
    protected $searchCriteriaBuilder;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Block\Data $directoryBlock,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        DistrictRepository $districtRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Cookie $cookie,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->directoryBlock  = $directoryBlock;
        $this->cookie          = $cookie;
        $this->scopeConfig     = $scopeConfig;
        $this->districtRepository = $districtRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;

    }

    public function getCurrency()
    {
        return $this->scopeConfig->getValue('currency/options/allow',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getCountries()
    {
        return $this->directoryBlock->getCountryHtmlSelect();
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        return $this->directoryBlock->getRegionHtmlSelect();
    }

    public function getTransiteoDistricts(){

        $searchCriteria = $this->searchCriteriaBuilder
            ->create();

       return $this->districtRepository->getList($searchCriteria)->getItems();

    }

    /**
     * @param $value
     *
     * @return string
     */
    public function getCookie(string $value)
    {
        return $this->cookie->get($value);
    }
}
