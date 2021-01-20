<?php
/**
 * @author Joris HART <jhart@efaktory.fr>
 * @copyright Copyright (c) 2020 eFaktory (https://www.efaktory.fr)
 * @link https://www.efaktory.fr
 */
declare(strict_types=1);

namespace Transiteo\CrossBorder\Plugin;

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
