<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\DutiesTaxesCalculator\Block\Adminhtml\Form\Field;

use Transiteo\DutiesTaxesCalculator\Model\Config\Source\TransiteoOrderStatus;

class OrderStatusColumn extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory
     */
    protected $orderStatusCollectionFactory;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param array $data
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $orderStatusCollectionFactory
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        array $data = [],
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $orderStatusCollectionFactory
    )
    {
        $this->orderStatusCollectionFactory = $orderStatusCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {

        if (!$this->getOptions()) {
            /**
             * @var \Magento\Sales\Model\ResourceModel\Order\Status\Collection $statusCollection
             */
            $statusCollection = $this->orderStatusCollectionFactory->create();
            $this->setOptions($statusCollection->toOptionArray());
        }
        return parent::_toHtml();
    }

}
