<?php

namespace Transiteo\CrossBorder\Controller\Adminhtml\Activation;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    const ADMIN_RESOURCE = 'Transiteo_CrossBorder::activation';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        //$resultPage->setActiveMenu(static::ADMIN_RESOURCE);
        //$resultPage->addBreadcrumb(__('Jobs'), __('Jobs'));
        //$resultPage->addBreadcrumb(__('Manage Departments'), __('Manage Departments'));
        $resultPage->getConfig()->getTitle()->prepend(__('Activation'));


        return $resultPage;
    }

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed(static::ADMIN_RESOURCE);
    }
}
