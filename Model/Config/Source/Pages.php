<?php declare(strict_types=1);
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

namespace Transiteo\LandedCost\Model\Config\Source;

class Pages implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'disable',
                'label' => __('Disable')
            ],
//            [
//                'value' => 'plp',
//                'label' => __('Product Listing Page')
//            ],
            [
                'value' => 'pdp',
                'label' => __('Product Page')
            ],
            [
                'value' => 'cart',
                'label' => __('Checkout Cart Page')
            ],
            [
                'value' => 'checkout',
                'label' => __('Checkout Payment Page')
            ],
        ];
    }
}
