<?php

namespace Transiteo\Taxes\Setup;

use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;
use Transiteo\Taxes\Setup\Patch\Data\InstallTransiteoDistrictData;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{

    /**
     * @var InstallTransiteoDistrictData
     */
    protected $transiteoDistrictData;

    /**
     * InstallTransiteoDistrictData constructor.
     *
     * @param InstallTransiteoDistrictData $transiteoDistrictData
     */
    public function __construct(
        InstallTransiteoDistrictData $transiteoDistrictData
    ) {
        $this->transiteoDistrictData = $transiteoDistrictData;
    }

    /**
     * @inheritDoc
     */
    public function upgrade(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        //upgrade district database
        $this->transiteoDistrictData->apply();
    }
}
