<?php
/*
 * @copyright Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Transiteo\DutiesTaxesCalculator\Model\Config\Backend\Currency;

class Cron extends \Magento\Framework\App\Config\Value
{
    const CRON_STRING_PATH = 'crontab/default/jobs/currency_rates_update/schedule/cron_expr';

    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $_configValueFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_configValueFactory = $configValueFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * After save handler
     *
     * @return \Magento\Config\Model\Config\Backend\Currency\Cron
     * @throws \Exception
     */
    public function afterSave()
    {
        $time      = $this->getData('groups/import/fields/time/value');
        $frequency = $this->getData('groups/import/fields/frequency/value');

        $frequencyHourly  = \Transiteo\DutiesTaxesCalculator\Plugin\AddHourlyCurrencyCronUpdate::CRON_HOURLY;
        $frequencyWeekly  = \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY;
        $frequencyMonthly = \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY;

        $cronExprArray = [
            (int)$time[1],                                        # Minute
            $frequency == $frequencyHourly ? '*' : (int)$time[0], # Hour
            $frequency == $frequencyMonthly ? '1' : '*',          # Day of the Month
            '*',                                                  # Month of the Year
            $frequency == $frequencyWeekly ? '1' : '*',           # Day of the Week
        ];

        $cronExprString = join(' ', $cronExprArray);

        try {
            /** @var $configValue \Magento\Framework\App\Config\ValueInterface */
            $configValue = $this->_configValueFactory->create();
            $configValue->load(self::CRON_STRING_PATH, 'path');
            $configValue->setValue($cronExprString)->setPath(self::CRON_STRING_PATH)->save();
        } catch (\Exception $e) {
            throw new \Exception(__('We can\'t save the Cron expression.'));
        }

        return parent::afterSave();
    }
}
