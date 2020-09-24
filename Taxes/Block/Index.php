<?php

namespace Transiteo\Taxes\Block;

use Transiteo\Taxes\Controller\Cookie;

class Index extends \Magento\Framework\View\Element\Template
{
    protected $directoryBlock;
    protected $_isScopePrivate;
    protected $cookie;
    
    public function __construct(
         \Magento\Framework\View\Element\Template\Context $context,
         \Magento\Directory\Block\Data $directoryBlock,
         Cookie $cookie,
         array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->directoryBlock = $directoryBlock;
        $this->cookie = $cookie;
    }
 
    public function getCountries()
    {
        $country = $this->directoryBlock->getCountryHtmlSelect();
        return $country;
    }
    public function getRegion()
    {
        $region = $this->directoryBlock->getRegionHtmlSelect();
        return $region;
    }

    public function getCookie($value)
    {
        $getCookie = $this->cookie->get($value);
        return $getCookie;
    }
}