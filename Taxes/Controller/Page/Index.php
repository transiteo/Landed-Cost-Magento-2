<?php

namespace Transiteo\Taxes\Controller\Page;

use Transiteo\Taxes\Controller\Cookie;
use Magento\Framework\App\Action\Context;
use Magento\Framework\File\Csv;
use Magento\Framework\Controller\ResultFactory;

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
        $this->cookie();
        return $this->getRequest()->getPost('currency');
    }

    public function cookie()
    {
        $setCookie = $this->cookie->set(self::COOKIE_NAME, $this->getRequest()->getPost('currency'));
        return $setCookie;
    }

    public function saveRegionsAndCountries()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $response = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        
        $response->setHeaders('Content-type', 'text/plain');
        $response->setContents(
            $this->_jsonHelper->jsonEncode([

            ])
            );
        return $response;
        // $result = $_product->getResource()->getAttribute('material')->getFrontend()->getValue($currencyValue);
    }

    public function loadcsvFile($file)
    {
        if(!isset($file['tmp_name']))
        throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));

        $csvData = $this->csv->getData($file['tmp_name']);

        $Dataofcsvfile = [];

        foreach ($csvData as $row => $data) {
            if ($row > 0){
                // $this->saveRegionsAndCountries($Dataofcsvfile);
            }
        }
        die();
    }
}