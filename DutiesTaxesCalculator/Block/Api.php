<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\DutiesTaxesCalculator\Block;

class Api extends \Magento\Framework\View\Element\Template
{
    protected $_isScopePrivate;

    public function __construct(
         \Magento\Framework\View\Element\Template\Context $context,
         array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    public function getTaxesApi()
    {
        $getApiPrice = '6 euros';
        return $getApiPrice;
    }
}
