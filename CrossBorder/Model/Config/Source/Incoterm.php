<?php
/**
 * @author Joris HART <jhart@efaktory.fr>
 * @copyright Copyright (c) 2020 eFaktory (https://www.efaktory.fr)
 * @link https://www.efaktory.fr
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