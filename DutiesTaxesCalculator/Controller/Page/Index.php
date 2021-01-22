<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\DutiesTaxesCalculator\Controller\Page;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\File\Csv;
use Transiteo\DutiesTaxesCalculator\Controller\Cookie;

class Index extends \Magento\Framework\App\Action\Action
{
    const COOKIE_NAME = 'transiteo-popup-info';
    protected $cookie;
    protected $csv;
    protected $jsonResultFactory;

    public function __construct(
        Context $context,
        Cookie $cookie,
        Csv $csv,
        JsonFactory $jsonResultFactory
    ) {
        $this->cookie = $cookie;
        $this->csv = $csv;
        $this->jsonResultFactory = $jsonResultFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->cookie();
    }

    public function cookie()
    {
        $country_id = $this->getRequest()->getParam('country_id');
        $region = $this->getRequest()->getParam('state');
        $currency = $this->getRequest()->getParam('currency');
        $table = [$country_id, $region, $currency];
        $value = implode("_", $table);
        $this->cookie->set(self::COOKIE_NAME, $value);
        $result = $this->jsonResultFactory->create();
        $result->setData($value);
        return $result;
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
        if (!isset($file['tmp_name'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
        }

        $csvData = $this->csv->getData($file['tmp_name']);

        $Dataofcsvfile = [];

        foreach ($csvData as $row => $data) {
            if ($row > 0) {
                // $this->saveRegionsAndCountries($Dataofcsvfile);
            }
        }
        die();
    }
}
