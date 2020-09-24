<?php

namespace Transiteo\Taxes\Cron;

use Transiteo\Taxes\Model\GeoIp;
use Psr\Log\LoggerInterface;
use Transiteo\Taxes\Service\GeoIpUpdater;

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
