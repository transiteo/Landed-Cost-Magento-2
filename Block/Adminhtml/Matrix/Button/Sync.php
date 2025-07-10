<?php

    namespace Transiteo\LandedCost\Block\Adminhtml\Matrix\Button;

    use Magento\Backend\Block\Widget\Context;
    use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

    class Sync implements ButtonProviderInterface
    {
        private Context $context;

        /**
         * @param Context $context
         */
        public function __construct(
            Context $context
        ) {
            $this->context = $context;
        }

        public function getButtonData()
        {
            return [
                'label' => __('Synchronize All Categories'),
                'on_click' => sprintf("location.href = '%s';", $this->context->getUrlBuilder()->getUrl('transiteo/matrix/sync')),
                'class' => 'primary',
                'sort_order' => 10
            ];
        }
    }
