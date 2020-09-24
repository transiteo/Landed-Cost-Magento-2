<?php

namespace Transiteo\Taxes\Block;

use Transiteo\Taxes\Controller\Cookie;

class Index extends \Magento\Framework\View\Element\Template
{
    protected $directoryBlock;
    protected $_isScopePrivate;
    protected $cookie;
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Block\Data $directoryBlock,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Cookie $cookie,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->directoryBlock  = $directoryBlock;
        $this->cookie          = $cookie;
        $this->scopeConfig     = $scopeConfig;
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
