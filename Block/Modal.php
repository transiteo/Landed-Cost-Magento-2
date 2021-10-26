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

namespace Transiteo\LandedCost\Block;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Transiteo\LandedCost\Api\Data\DistrictInterface;
use Transiteo\LandedCost\Controller\Cookie;
use Transiteo\LandedCost\Model\DistrictRepository;
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
     * @var \Transiteo\LandedCost\Model\Config
     */
    protected $config;

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
     * @param \Transiteo\LandedCost\Model\Config $config
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
        \Transiteo\LandedCost\Model\Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
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
        $title = $this->scopeConfig->getValue('transiteo_landedcost_settings/modal/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $title === null ? null :  $this->escaper->escapeHtml($title);
    }

    /**
     * @return array|string
     */
    public function getModalButtonColor()
    {
        $buttonColor = $this->scopeConfig->getValue('transiteo_landedcost_settings/modal/button_color',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $buttonColor === null ? null :  $this->escaper->escapeHtml($buttonColor);
    }

    /**
     * @return array|string
     */
    public function getModalCss()
    {
        $css = $this->scopeConfig->getValue('transiteo_landedcost_settings/modal/css',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $css === null ? null :  $this->escaper->escapeHtml($css);
    }

    /**
     * @return bool
     */
    public function isActivatedOnProductPage(){
        return $this->config->isActivatedOnProductPage();
    }
}
