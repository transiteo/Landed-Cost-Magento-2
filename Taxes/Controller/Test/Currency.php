<?php

declare(strict_types=1);

namespace Transiteo\Taxes\Controller\Test;

use Transiteo\Taxes\Model\TransiteoApiService;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;



class View extends \Magento\Framework\App\Action\Action{

    protected $apiService;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        TransiteoApiService $apiService
    ) {
        $this->apiService = $apiService;
        parent::__construct($context);
    }

    public function execute(){
        
        /** @var Json $jsonResult */
        $jsonResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $jsonResult->setData([
            'rate' => $this->apiService->getCurrencyRate("EUR", "USD")
        ]);

        return $jsonResult;
    }

}