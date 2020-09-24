<?php

namespace Transiteo\Taxes\Controller\Page;

use Transiteo\Taxes\Controller\Cookie;
use Magento\Framework\App\Action\Context;
use Magento\Framework\File\Csv;

Class Index extends \Magento\Framework\App\Action\Action 
{
    const COOKIE_NAME = 'transiteo-country-currency';
    protected $cookie;
    protected $csv;

    public function __construct
    (
        Context $context,
        Cookie $cookie,
        Csv $csv
    )
    {
        $this->cookie = $cookie;
        $this->csv = $csv;
        parent::__construct($context);
    }

    public function execute()
    {
        // $this->cookie();
        $setCookie = $this->cookie->set(self::COOKIE_NAME, 'test');
        return $setCookie;
    }

    public function cookie()
    {
        $setCookie = $this->cookie->set(self::COOKIE_NAME, 'test');
        return $setCookie;
    }

    public function saveRegionsAndCountries()
    {

    }

    public function loadcsvFile($file)
    {
        if(!isset($file['tmp_name']))
        throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));

        $csvData = $this->csv->getData($file['tmp_name']);

        $Dataofcsvfile = [];

        foreach ($csvData as $row => $data) {
            if ($row > 0){
                //Start your work
                // $this->saveRegionsAndCountries($Dataofcsvfile);
            }
        }
        die();
    }
}