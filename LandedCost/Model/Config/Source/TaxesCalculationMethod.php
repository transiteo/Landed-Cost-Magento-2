<?php
/*
 * @copyright Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Transiteo\LandedCost\Model\Config\Source;

class TaxesCalculationMethod implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'CIF',
                'label' => __('CIF')
            ],
            [
                'value' => 'FOB',
                'label' => __('FOB')
            ]
        ];
    }

}
