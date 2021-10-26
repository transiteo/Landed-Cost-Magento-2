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
