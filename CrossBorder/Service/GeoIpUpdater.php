<?php
/*
 * @author Blackbird Agency, Joris HART
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>, <jhart@efaktory.fr>
 */
declare(strict_types=1);

namespace Transiteo\CrossBorder\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Psr\Log\LoggerInterface;

class GeoIpUpdater
{
    const GEOIP_DOWNLOADER_ENABLED = 'transiteo_activation/geoip/enable_geoip_download';

    const GEOIP_LICENCE_KEY = 'transiteo_activation/geoip/key';

    const GEOIP_DOWNLOAD_URL = 'download.maxmind.com/app/geoip_download';

    const GEOIP_DOWNLOAD_PATH = 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-Country&license_key=%1&suffix=tar.gz';

    const GEOIP_DOWNLOAD_FOLDER = 'transiteo-geoip';

    const GEOIP_FILENAME = 'GeoLite2-Country.mmdb';
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var WriteFactory
     */
    protected $dirWriter;

    /**
     * @var DriverPool
     */
    protected $driverPool;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlHelper $urlHelper,
        DirectoryList $directoryList,
        WriteFactory $dirWriter,
        DriverPool $driverPool,
        LoggerInterface $logger
    ) {
        $this->scopeConfig   = $scopeConfig;
        $this->urlHelper     = $urlHelper;
        $this->directoryList = $directoryList;
        $this->dirWriter     = $dirWriter;
        $this->driverPool    = $driverPool;
        $this->logger        = $logger;
    }

    /**
     * Execute function to download for the GeoIP database
     */
    public function execute()
    {
        // If not enabled, don't continue
        if ( ! $this->isEnabled()) {
            return;
        }

        $downloadPath = $this->getGeoIpVarDir();
        $httpDriver   = $this->driverPool->getDriver(DriverPool::HTTPS);
        $fileDriver   = $this->driverPool->getDriver(DriverPool::FILE);

        // Create the file if it doesnt exists.
        $downloadPath->touch('GeoLite2-Country.tar.gz');

        // Get Content of the Database
        $content = $httpDriver->fileGetContents($this->getDownloadUrl());

        // Save the archive to the directory
        $fileDriver->filePutContents($downloadPath->getAbsolutePath('GeoLite2-Country.tar.gz'), $content);

        try {
            // if phar not activated then we do it
            if ( ! in_array('phar', stream_get_wrappers(), true)) {
                stream_wrapper_restore('phar');
            }

            $phar = new \PharData($downloadPath->getAbsolutePath('GeoLite2-Country.tar.gz'));
            $phar->extractTo($downloadPath->getAbsolutePath(), null, true);

            // Move the file to
            $files = $downloadPath->search('**/*.mmdb');

            if (!empty($files)) {
                foreach ($files as $file) {
                    $filePath = $downloadPath->getAbsolutePath($file);

                    // Move the file to the parent directory.
                    $fileDriver->rename($filePath, $downloadPath->getAbsolutePath(self::GEOIP_FILENAME));
                }
            }

            // Clean the download directory
            $this->cleanDownloadDirectory($downloadPath);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Transiteo GeoIp Updater: ' . $e->getMessage());
        }
    }

    /**
     * @return bool|mixed|string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function getGeoIpDownloadedDatabase()
    {
        $downloadPath = $this->getGeoIpVarDir();

        // Move the file to
        $files = $downloadPath->search('*.mmdb');

        if (empty($files)) {
            return false;
        }

        return $downloadPath->getAbsolutePath($files[0]);
    }

    /**
     * @return mixed
     */
    private function isEnabled()
    {
        return $this->scopeConfig->getValue(self::GEOIP_DOWNLOADER_ENABLED);
    }

    /**
     * @return mixed
     */
    private function getLicenseKey()
    {
        return $this->scopeConfig->getValue(self::GEOIP_LICENCE_KEY);
    }

    /**
     * @return array
     */
    private function getUrlParameters(): array
    {
        return [
            'edition_id'  => 'GeoLite2-Country',
            'license_key' => $this->getLicenseKey(),
            'suffix'      => 'tar.gz'
        ];
    }

    /**
     * @return \Magento\Framework\Filesystem\Directory\Write
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function getGeoIpVarDir(): WriteInterface
    {
        $varDir       = $this->directoryList->getPath(DirectoryList::VAR_DIR);
        $downloadPath = $varDir . DIRECTORY_SEPARATOR . self::GEOIP_DOWNLOAD_FOLDER;

        return $this->dirWriter->create($downloadPath);
    }

    /**
     * get .tar.gz file on maxmind website
     *
     * @return string
     */
    private function getDownloadUrl(): string
    {
        $url = self::GEOIP_DOWNLOAD_URL;

        return $this->urlHelper->addRequestParam($url, $this->getUrlParameters());
    }

    /**
     * @param WriteInterface $downloadDirectory
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function cleanDownloadDirectory(WriteInterface $downloadDirectory)
    {
        $directories = $downloadDirectory->search('**/');

        foreach ($directories as $directory) {
            $downloadDirectory->delete($directory);
        }

        $archives = $downloadDirectory->search('*.tar.gz');

        foreach ($archives as $archive) {
            $downloadDirectory->delete($archive);
        }
    }
}
