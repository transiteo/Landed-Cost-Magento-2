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

namespace Transiteo\LandedCost\Setup;

use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;
use Transiteo\LandedCost\Setup\Patch\Data\InstallTransiteoDistrictData;

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
