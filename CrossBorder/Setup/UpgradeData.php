<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\CrossBorder\Setup;

use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;
use Transiteo\CrossBorder\Setup\Patch\Data\InstallTransiteoDistrictData;

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
