<?php
/**
 * @author Joris HART <jhart@efaktory.fr>
 * @copyright Copyright (c) 2020 eFaktory (https://www.efaktory.fr)
 * @link https://www.efaktory.fr
 */
declare(strict_types=1);

namespace Transiteo\Taxes\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Transiteo\Taxes\Service\GeoIpUpdater as GeoIpUpdaterService;

class GeoIpUpdater extends Command
{
    /**
     * @var GeoIpUpdaterService
     */
    protected $geoIpUpdater;

    /**
     * GeoIpUpdater constructor.
     *
     * @param GeoIpUpdaterService $geoIpUpdater
     * @param string|null $name
     */
    public function __construct(
        GeoIpUpdaterService $geoIpUpdater,
        string $name = null
    ) {
        parent::__construct($name);

        $this->geoIpUpdater = $geoIpUpdater;
    }

    protected function configure()
    {
        $this->setName('transiteo:geoip:update');
        $this->setDescription('Download an updated database from the Maxmind service');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->geoIpUpdater->execute();
    }
}
