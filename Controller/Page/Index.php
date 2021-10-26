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

namespace Transiteo\LandedCost\Controller\Page;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\File\Csv;
use Transiteo\LandedCost\Service\TaxesService;

class Index extends \Magento\Framework\App\Action\Action
{
    const COOKIE_NAME = 'transiteo-popup-info';
    protected $cookie;
    protected $csv;
    protected $jsonResultFactory;
    /**
     * @var TaxesService
     */
    protected $taxesService;

    public function __construct(
        Context $context,
        Csv $csv,
        JsonFactory $jsonResultFactory,
        TaxesService $taxesService
    ) {
        $this->taxesService = $taxesService;
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
        $value = $this->taxesService->updateCookieValue($country_id ?? false, $region ?? false, $currency ??false);
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
