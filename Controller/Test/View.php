<?php

declare(strict_types=1);

namespace Transiteo\Taxes\Controller\Test;

use Transiteo\Taxes\Model\GeoIp;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;



class View extends \Magento\Framework\App\Action\Action{

    protected $geoIp;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        GeoIp $geoIp
    ) {
        $this->geoIp = $geoIp;
        parent::__construct($context);
    }

    public function execute(){
        
        if(!$this->geoIp->checkisExtracted())
            $this->geoIp->updateDatabase();
        
        /** @var Json $jsonResult */
        $jsonResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $jsonResult->setData([
            'iso_code' => $this->geoIp->getUserCountry()
        ]);

        return $jsonResult;
    }

}