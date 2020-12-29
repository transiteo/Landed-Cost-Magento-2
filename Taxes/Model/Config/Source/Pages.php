<?php declare(strict_types=1);

namespace Transiteo\Taxes\Model\Config\Source;

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
//            [
//                'value' => 'pdp',
//                'label' => __('Product Page')
//            ],
            [
                'value' => 'cart',
                'label' => __('Cart Page')
            ],
            [
                'value' => 'checkout',
                'label' => __('Checkout Page')
            ],
        ];
    }
}
