<?php

    namespace Transiteo\LandedCost\Block\Adminhtml\Matrix\Button;

    use Magento\Backend\Block\Widget\Context;
    use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

    class SyncDropdown implements ButtonProviderInterface
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

        public function getButtonData(): array
        {
            return [
                'label' => __('Update For ...'),
                'id' => 'categorySyncDropdownToggle',
                'class' => 'secondary',
                'sort_order' => 10,
                'type' => 'button',
                'on_click' => 'return false;',
            ];
        }
    }
