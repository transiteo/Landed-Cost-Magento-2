<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\DutiesTaxesCalculator\Cron;

use Transiteo\DutiesTaxesCalculator\Model\GeoIp;
use Psr\Log\LoggerInterface;
use Transiteo\DutiesTaxesCalculator\Service\GeoIpUpdater;

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
