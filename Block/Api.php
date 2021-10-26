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
