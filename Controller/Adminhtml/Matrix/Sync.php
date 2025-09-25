<?php

    namespace Transiteo\LandedCost\Controller\Adminhtml\Matrix;

    use Magento\Backend\App\Action;
    use Magento\Backend\App\Action\Context;
    use Transiteo\LandedCost\Model\Config;
    use Transiteo\LandedCost\Service\CategorySync;

    class Sync extends Action
    {
        public function __construct(
            Context $context,
            private CategorySync $categorySyncService,
            protected Config $config
        ) {
            parent::__construct($context);
        }

        public function execute()
        {
            $country = $this->getRequest()->getParam('country');

            if(!$this->config->isSyncCategoryEnabled()){
                $this->messageManager->addErrorMessage(__('Synchronization of categories is disabled.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('transiteo/matrix/index');
            }

            if (!empty($country)) {
                $this->categorySyncService->addListCategoryToAsync($country);
            } else {
                $this->categorySyncService->addCategoriesToAsync();
            }

            $this->messageManager->addSuccessMessage(__('Synchronization added to queue.'));

            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('transiteo/matrix/index');
        }
    }
