<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\DutiesTaxesCalculator\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class RemoveBlock implements ObserverInterface
{
    protected $_scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();
        $block = $layout->getBlock('transiteo.modal');  // here block reference name to remove

        $show = $this->_scopeConfig->getValue('transiteo_settings/modal/show', ScopeInterface::SCOPE_STORE);
        if ($block) {
            if (!$show) {
                $layout->unsetElement('transiteo.modal');
            }
        }


        $block = $layout->getBlock('transiteo.modal.button.link');  // here block reference name to remove

        if ($block) {
            if (!$show) {
                $layout->unsetElement('transiteo.modal.button.link');

            }
        }
    }
}
