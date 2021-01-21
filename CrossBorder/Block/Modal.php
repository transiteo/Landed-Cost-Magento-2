<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\CrossBorder\Block;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Transiteo\CrossBorder\Api\Data\DistrictInterface;
use Transiteo\CrossBorder\Controller\Cookie;
use Transiteo\CrossBorder\Model\DistrictRepository;
use Magento\Framework\Escaper;

class Modal extends \Magento\Framework\View\Element\Template
{
    protected $directoryBlock;

    protected $_isScopePrivate;

    protected $cookie;

    protected $scopeConfig;

    protected $districtRepository;

    protected $searchCriteriaBuilder;

    /**
     * @var
     */
    protected $escaper;

    /**
     * Modal constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Block\Data $directoryBlock
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param DistrictRepository $districtRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Cookie $cookie
     * @param \Magento\Framework\Escaper $escaper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Block\Data $directoryBlock,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        DistrictRepository $districtRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Cookie $cookie,
        Escaper $escaper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_isScopePrivate       = true;
        $this->directoryBlock        = $directoryBlock;
        $this->cookie                = $cookie;
        $this->scopeConfig           = $scopeConfig;
        $this->districtRepository    = $districtRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->escaper               = $escaper;

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

    public function getTransiteoDistricts()
    {

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

    /**
     * @return array|string
     */
    public function getModalTitle()
    {
        $title = $this->scopeConfig->getValue('transiteo_settings/modal/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $title === null ? null :  $this->escaper->escapeHtml($title);
    }

    /**
     * @return array|string
     */
    public function getModalButtonColor()
    {
        $buttonColor = $this->scopeConfig->getValue('transiteo_settings/modal/button_color',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $buttonColor === null ? null :  $this->escaper->escapeHtml($buttonColor);
    }

    /**
     * @return array|string
     */
    public function getModalCss()
    {
        $css = $this->scopeConfig->getValue('transiteo_settings/modal/css',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $css === null ? null :  $this->escaper->escapeHtml($css);
    }
}
