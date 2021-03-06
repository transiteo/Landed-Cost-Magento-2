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

namespace Transiteo\LandedCost\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Module\Dir as ModuleDir;
use MaxMind\Db\Reader as MaxMindReader;
use PhpParser\Node\Expr\AssignOp\Mod;
use Transiteo\LandedCost\Service\GeoIpUpdater;

class GeoIp
{
    const DOWNLOAD_URL = 'https://download.maxmind.com';

    private $licenseKey = "uznrIg04Y1cusqEB"; //linked to crol@efaktory.fr

    private $dirPath = __DIR__ . "/../downloads/";

    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var
     */
    protected $moduleDir;

    /**
     * @var GeoIpUpdater
     */
    protected $geoIpService;

    /**
     * GeoIp constructor.
     *
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(
        RemoteAddress $remoteAddress,
        ScopeConfigInterface $scopeConfig,
        ModuleDir $moduleDir,
        GeoIpUpdater $geoIpService
    ) {
        $this->remoteAddress = $remoteAddress;
        $this->scopeConfig   = $scopeConfig;
        $this->moduleDir     = $moduleDir;
        $this->geoIpService  = $geoIpService;
    }

    /**
     * @return string
     */
    protected function getDatabaseFile(): string
    {
        if ($databasePath = $this->geoIpService->getGeoIpDownloadedDatabase()) {
            return $databasePath;
        }

        $modulePath = $this->moduleDir->getDir('Transiteo_LandedCost');

        return $modulePath . DIRECTORY_SEPARATOR . 'lib/internal/GeoLite2-Country.mmdb';
    }

    /**
     * return the user country based on his ip address
     *
     * @return mixed|null
     * @throws MaxMindReader\InvalidDatabaseException
     */
    public function getUserCountry()
    {
        $fileName = $this->getDatabaseFile();

        $reader = new MaxMindReader($fileName);

        $ipAddress = $this->getUserIp();
        //$ipAddress = "82.65.140.34";

        $country = $reader->get($ipAddress);
        $reader->close();

        return $country ? $country['country']['iso_code'] : null;
    }

    /**
     * return the visitor ip address
     *
     * @return bool|string
     */
    public function getUserIp()
    {
        return $this->remoteAddress->getRemoteAddress();
    }
}
