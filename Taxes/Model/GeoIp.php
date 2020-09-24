<?php

namespace Transiteo\Taxes\Model;
require(__DIR__.'/../vendor/MaxMind-DB-Reader-php-master/autoload.php');

use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use MaxMind\Db\Reader;

class GeoIp
{
    private $licenseKey = "uznrIg04Y1cusqEB"; //linked to crol@efaktory.fr
    private $dirPath = __DIR__."/../downloads/";

    // get .tar.gz file on maxmind website
    public function getDownloadPath(){

        return 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-Country&license_key='.$this->licenseKey.'&suffix=tar.gz';
    }


    // download and extract geoip database
    public function updateDatabase(){

        $this->cleanDownloadDirectory();
    
        try {
            file_put_contents($this->dirPath . 'GeoLite2-Country.tar.gz', fopen($this->getDownloadPath(), 'r'));

            // if phar not activated then we do it
            if (!in_array('phar', stream_get_wrappers(), true)) {
                stream_wrapper_restore('phar');
            }

            $phar = new \PharData($this->dirPath . 'GeoLite2-Country.tar.gz');
            $phar->extractTo($this->dirPath);
            
            return true;

        } catch (\Exception $e) {
            
            return false;
        }

        
    }


    // return if .tar.gz file existe
    public function checkIsDownloaded()
    {
        if (!file_exists($this->dirPath)) {
            return false;
        }

        $folder   = scandir($this->dirPath, true);
        $pathFile = $this->dirPath.'GeoLite2-Country.tar.gz';
        if (!file_exists($pathFile)) {
            return false;
        }

        return true;
    }

    // return if .mmdb file exist
    public function checkisExtracted(){

        if (!file_exists($this->dirPath)) {
            return false;
        }

        $folder   = scandir($this->dirPath, true);
        $pathFile = $this->dirPath.'/'.$folder[0].'/GeoLite2-Country.mmdb';

        if (!file_exists($pathFile))
            return false;
        else
            return $pathFile;

    }

    // clean download directory
    public function cleanDownloadDirectory(){

        $folder   = scandir($this->dirPath, true);

        $pathFile = $this->dirPath . '/' . $folder[0] . '/GeoLite2-Country.mmdb';

        if (file_exists($pathFile)) {
            foreach (scandir($this->dirPath . '/' . $folder[0], true) as $filename) {
                if ($filename == '..' || $filename == '.') {
                    continue;
                }
                unlink($this->dirPath . '/' . $folder[0] . '/' . $filename);
            }
            rmdir($this->dirPath . '/' . $folder[0]);
        }
    }

    // return the user country based on his ip address
    public function getUserCountry(){

        $fileName = $this->checkisExtracted();

        if($fileName){
            $reader = new Reader($fileName);

            //$ipAddress = $this->getUserIp();
            $ipAddress = "216.58.204.100";
            
            $country = $reader->get($ipAddress);
            $reader->close();

            if($country){
                $isoCodeCountry = $country['country']['iso_code'];
                return $isoCodeCountry;
            }
            else{
                return null;
            }
    
        }else
            return null;
            
    }

    // return the visitor ip address
    public function getUserIp(){

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $obj = $om->get('Magento\Framework\HTTP\PhpEnvironment\RemoteAddress');
        $ip =  $obj->getRemoteAddress();

        return $ip;
    }

}