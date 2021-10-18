<?php
/*
 * @copyright Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Transiteo\LandedCost\Model\Config\Source;

class Incoterm implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'ddu',
                'label' => __('DDU')
            ],
            [
                'value' => 'ddp',
                'label' => __('DDP')
            ]
        ];
    }

}
