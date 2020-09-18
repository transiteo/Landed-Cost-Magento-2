<?php
namespace Transiteo\Taxes\Cron;

use Transiteo\Taxes\Model\GeoIp;
use Psr\Log\LoggerInterface;

class UpdateGeoIpDB {
    protected $logger;
    private $geoIp;

    public function __construct(
        LoggerInterface $logger,
        GeoIp $geoIp
        ) {
        $this->logger = $logger;
        $this->geoIp = $geoIp;
    }

   /**
    * Write to system.log
    *
    * @return void
    */
    public function execute() {

        if($this->geoIp->updateDatabase())
            $this->logger->info('GeoIp Database updated !');
        else
            $this->logger->info('Error during update of GeoIp Database !');
    }
}
