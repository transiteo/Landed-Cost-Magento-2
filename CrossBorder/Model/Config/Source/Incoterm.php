<?php
/*
 * @copyright Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Transiteo\CrossBorder\Model\Config\Source;

class Incoterm implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'dap',
                'label' => __('DAP')
            ],
            [
                'value' => 'ddp',
                'label' => __('DDP')
            ]
        ];
    }

}
