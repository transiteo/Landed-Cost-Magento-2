<?php

declare(strict_types=1);

namespace Transiteo\Taxes\Controller\Test;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Transiteo\Base\Model\TransiteoApiService;

class Index extends \Magento\Framework\App\Action\Action{

    protected $api;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        TransiteoApiService $api
    ) {
        $this->api = $api;
        parent::__construct($context);
    }

    public function execute(){

            /** @var Json $jsonResult */
        $jsonResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $jsonResult->setJsonData($this->api->getDuties([]));


        
        return $jsonResult;

       // return $this->api->getIdToken();
        //return $this->api->getDuties([]);
    }


}