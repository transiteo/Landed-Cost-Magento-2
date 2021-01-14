<?php
/**
 * @author Joris HART <jhart@efaktory.fr>
 * @copyright Copyright (c) 2020 eFaktory (https://www.efaktory.fr)
 * @link https://www.efaktory.fr
 */
declare(strict_types=1);

namespace Transiteo\Taxes\Ui\Component\Select\Source;

class Incoterm implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'dap',
                'label' => __('No')
            ],
            [
                'value' => 'ddp',
                'label' => __('Yes')
            ]
        ];
    }

}
