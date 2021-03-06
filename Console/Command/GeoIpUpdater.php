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
declare(strict_types=1);

namespace Transiteo\LandedCost\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Transiteo\LandedCost\Service\GeoIpUpdater as GeoIpUpdaterService;

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
