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

namespace Transiteo\LandedCost\Plugin;

use Magento\Cron\Model\Config\Source\Frequency;

class AddHourlyCurrencyCronUpdate
{
    const CRON_HOURLY = 'H';

    public function afterToOptionArray(
        Frequency $subject,
        array $result
    ) {

        array_unshift($result, [
            'label' => __('Hourly'),
            'value' => self::CRON_HOURLY
        ]);

        return $result;
    }
}
