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

namespace Transiteo\LandedCost\Block\Adminhtml\Form\Field;

use Transiteo\LandedCost\Model\Config\Source\TransiteoOrderStatus;

class TransiteoOrderStatusColumn extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var TransiteoOrderStatus
     */
    protected $orderStatus;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        array $data = [],
        TransiteoOrderStatus $orderStatus
    )
    {
        $this->orderStatus = $orderStatus;
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
            $this->setOptions($this->orderStatus->toOptionArray());
        }
        return parent::_toHtml();
    }

}
