<?php declare(strict_types=1);
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
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
