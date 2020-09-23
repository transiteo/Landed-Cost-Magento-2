<?php

declare(strict_types=1);

namespace Transiteo\Taxes\Controller\Test;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Transiteo\Base\Model\TransiteoApiService;

class Currency extends \Magento\Framework\App\Action\Action{

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
            'rate' => $this->apiService->getCurrencyRate("EUR", "USD", 0)
        ]);

        return $jsonResult;
    }

}