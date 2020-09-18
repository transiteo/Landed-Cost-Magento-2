<?php

declare(strict_types=1);

namespace Transiteo\Taxes\Controller\Page;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Transiteo\Taxes\Model\TransiteoApiService;


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

          

        return $this->api->getDuties([]);
    }


}