<?php

namespace Transiteo\Taxes\Controller\Page;

use Magento\Framework\App\Action\Context;

Class States extends \Magento\Framework\App\Action\Action 
{
    public function __construct(
        Context       $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Directory\Model\RegionFactory $regionColFactory,
        PageFactory $resultPageFactory
    ) {        
        $this->regionColFactory = $regionColFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();

        $result= $this->resultJsonFactory->create();
        $regions=$this->regionColFactory->create()->getCollection()->addFieldToFilter('country_id',$this->getRequest()->getParam('country'));
        return $result->setData(['success' => true,'value'=>$regions->getData()]);
    }
}