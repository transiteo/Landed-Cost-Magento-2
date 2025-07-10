<?php

    namespace Transiteo\LandedCost\Controller\Adminhtml\Matrix;

    use Magento\Backend\App\Action;
    use Magento\Backend\App\Action\Context;
    use Transiteo\LandedCost\Service\CategorySync;

    class Sync extends Action
    {
        public function __construct(
            Context $context,
            private CategorySync $categorySyncService
        ) {
            parent::__construct($context);
        }

        public function execute()
        {
            $country = $this->getRequest()->getParam('country');

            if (!empty($country)) {
                $this->categorySyncService->getListOfCategories($country);
            } else {
                $this->categorySyncService->actionOnCategories();
            }

            $this->messageManager->addSuccessMessage(__('Synchronization added to queue.'));



            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('transiteo/matrix/index');
        }
    }
