<?php

declare(strict_types=1);

namespace Transiteo\Taxes\Controller\Page;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;

class View2 extends Action{

    public function execute(){

        /** @var Json $jsonResult */
        $jsonResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $jsonResult->setData([
            'message' => 'test'
        ]);

        return $jsonResult;
    }

}