<?php

declare(strict_types=1);

namespace Transiteo\Taxes\Controller\Test;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Transiteo\Taxes\Model\TransiteoSingleProduct;

class Single extends Action{

    protected $singleProduct;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        TransiteoSingleProduct $singleProduct
    ) {
        $this->singleProduct = $singleProduct;
        parent::__construct($context);
    }

    public function execute(){

        /** @var Json $jsonResult */
        $jsonResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $jsonResult->setData([
            "duty" => $this->singleProduct->getDuty(),
            "vat" => $this->singleProduct->getVat(),
            "special_taxes" => $this->singleProduct->getSpecialTaxes(),
            "total" => $this->singleProduct->getTotalTaxes()
            ]);
        
        return $jsonResult;

    }


}