<?php
/*
 * Transiteo LandedCost
 *
 * NOTICE OF LICENSE
 * if you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@bird.eu so we can send you a copy immediately.
 * @category      Transiteo
 * @package       Transiteo_LandedCost
 * @copyright    Open Software License (OSL 3.0)
 * @author          Blackbird Team
 * @license          MIT
 * @support        https://github.com/transiteo/Landed-Cost-Magento-2/issues/new/
 */

declare(strict_types=1);

namespace Transiteo\LandedCost\Block\Adminhtml\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Transiteo\LandedCost\Model\Config\Source\TransiteoOrderStatus;

/**
 *
 */
class OrderStatusCorrespondence extends AbstractFieldArray
{

    /**
     * @var TransiteoOrderStatusColumn
     */
    protected $transiteoOrderStatusColumnRenderer;


    /**
     * @var OrderStatusColumn
     */
    protected $orderStatusColumn;


    protected function _prepareToRender()
    {
        $this->addColumn(
            'magento_status',
            [
                'label' => __('Order Status'),
                'class' => 'required-entry',
//                'renderer' => $this->getOrderStatusColumnRenderer()
            ]
        );
        $this->addColumn('transiteo_status',
            [
                'label' => __('Transiteo Order Status'),
                'class' => 'required-entry',
                'renderer' => $this->getTransiteoOrderStatusColumnRenderer()
            ]
        );

        $this->_addAfter = false;
    }

    /**
     * @return TransiteoOrderStatusColumn
     * @throws LocalizedException
     */
    protected function getTransiteoOrderStatusColumnRenderer():TransiteoOrderStatusColumn
    {
        if (!$this->transiteoOrderStatusColumnRenderer) {
            $this->transiteoOrderStatusColumnRenderer = $this->getLayout()->createBlock(
                TransiteoOrderStatusColumn::class,
                '',
                []
            );
        }
        return $this->transiteoOrderStatusColumnRenderer;
    }

    /**
     * @return OrderStatusColumn
     * @throws LocalizedException
     */
    protected function getOrderStatusColumnRenderer():OrderStatusColumn
    {
        if (!$this->orderStatusColumn) {
            $this->orderStatusColumn = $this->getLayout()->createBlock(
                OrderStatusColumn::class,
                '',
                []
            );
        }
        return $this->orderStatusColumn;
    }

}
