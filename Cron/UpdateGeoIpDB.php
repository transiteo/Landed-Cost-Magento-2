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

namespace Transiteo\LandedCost\Cron;

use Transiteo\LandedCost\Model\GeoIp;
use Psr\Log\LoggerInterface;
use Transiteo\LandedCost\Service\GeoIpUpdater;

class UpdateGeoIpDB
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var GeoIpUpdater
     */
    private $geoIpUpdater;

    /**
     * UpdateGeoIpDB constructor.
     *
     * @param LoggerInterface $logger
     * @param GeoIpUpdater $geoIpUpdater
     */
    public function __construct(
        LoggerInterface $logger,
        GeoIpUpdater $geoIpUpdater
    ) {
        $this->logger       = $logger;
        $this->geoIpUpdater = $geoIpUpdater;
    }

    /**
     * Write to system.log
     *
     * @return void
     */
    public function execute()
    {
        if ($this->geoIpUpdater->execute()) {
            $this->logger->info('GeoIp Database updated !');
        } else {
            $this->logger->info('Error during update of GeoIp Database !');
        }
    }
}
