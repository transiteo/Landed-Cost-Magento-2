<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

declare(strict_types=1);

namespace Transiteo\LandedCost\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class TransiteoOrderStatus implements OptionSourceInterface
{

    public function toOptionArray()
    {
        $options = [];
        foreach (\Transiteo\LandedCost\Model\Config::TRANSITEO_ORDER_STATUS as $status){
            $options[] = [
                'label' => $status,
                'value' => $status
            ];
        }
        return $options;
    }
}
