<?php

    namespace Transiteo\LandedCost\Controller\Adminhtml\Matrix;

    use Magento\Backend\App\Action;
    use Magento\Backend\App\Action\Context;
    use Magento\Framework\View\Result\PageFactory;

    class Index extends Action
    {
        protected PageFactory $resultPageFactory;

        public function __construct(Context $context, PageFactory $resultPageFactory)
        {
            parent::__construct($context);
            $this->resultPageFactory = $resultPageFactory;
        }

        public function execute()
        {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Transiteo_LandedCost::category_matrix');
            $resultPage->getConfig()->getTitle()->prepend(__('Category Matrix'));
            return $resultPage;
        }
    }
