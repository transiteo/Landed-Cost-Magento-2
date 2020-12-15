<?php

namespace Transiteo\Taxes\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Module\Dir as ModuleDir;
use MaxMind\Db\Reader as MaxMindReader;
use PhpParser\Node\Expr\AssignOp\Mod;
use Transiteo\Taxes\Service\GeoIpUpdater;

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

        $modulePath = $this->moduleDir->getDir('Transiteo_Taxes');

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
        $ipAddress = "82.65.140.34";

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
