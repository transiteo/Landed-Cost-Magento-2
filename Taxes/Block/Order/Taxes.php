<?php

namespace Transiteo\Taxes\Block\Order;

class Taxes extends \Magento\Framework\View\Element\Template
{
    /**
     * Tax configuration model
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $_config;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        array $data = []
    ) {
        $this->_config = $taxConfig;
        parent::__construct($context, $data);
    }

    /**
     * Check if we nedd display full tax total info
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->_source;
    }

    public function getStore()
    {
        return $this->_order->getStore();
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Initialize all order totals relates with tax
     *
     * @return \Magento\Tax\Block\Sales\Order\Tax
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
        $order = $this->_order;

        $store = $this->getStore();

        $transiteoTotalTaxes = $order->getTransiteoTotalTaxes();
        $baseTransiteoTotalTaxes = $order->getBaseTransiteoTotalTaxes();

        //////////////////LOGGER//////////////
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('Init Totals : ' . $transiteoTotalTaxes);
        ///////////////////////////////////////

        if ($transiteoTotalTaxes) {
            $totals = [];
            $transiteoDuty = $order->getTransiteoDuty();
            $baseTransiteoDuty = $order->getBaseTransiteoDuty();
            $transiteoVat = $order->getTransiteoVat();
            $baseTransiteoVat = $order->getBaseTransiteoVat();
            $transiteoSpecialTaxes = $order->getTransiteoSpecialTaxes();
            $baseTransiteoSpecialTaxes = $order->getBaseTransiteoSpecialTaxes();

            if (!($transiteoVat != 0 xor $transiteoDuty != 0 xor $transiteoSpecialTaxes != 0)) {
                $totals['transiteo_total_taxes'] = new \Magento\Framework\DataObject(
                    [
                        'code' => 'transiteo_total_taxes',
                        'field' => 'transiteo_total_taxes_amount',
                        'strong' => true,
                        'value' => $transiteoTotalTaxes,
                        'base_value' => $baseTransiteoTotalTaxes,
                        'label' => __('Cross Border Total Taxes'),
                    ]
                );
            }

            if ($transiteoSpecialTaxes && $transiteoSpecialTaxes!=0) {
                $totals['transiteo_special_taxes'] = new \Magento\Framework\DataObject(
                    [
                        'code' => 'transiteo_special_taxes',
                        'field' => 'transiteo_special_taxes_amount',
                        'value' => $transiteoSpecialTaxes,
                        'base_value' => $baseTransiteoSpecialTaxes,
                        'label' => __('Cross Border Special Taxes'),
                    ]
                );
            }

            if ($transiteoVat && $transiteoVat!=0) {
                $totals['transiteo_vat'] = new \Magento\Framework\DataObject(
                    [
                        'code' => 'transiteo_vat',
                        'field' => 'transiteo_vat_amount',
                        'value' => $transiteoVat,
                        'base_value' => $baseTransiteoVat,
                        'label' => __('Cross Border VAT / GST'),
                    ]
                );
            }

            if ($transiteoDuty && $transiteoDuty!=0) {
                $totals['transiteo_duty'] = new \Magento\Framework\DataObject(
                    [
                        'code' => 'transiteo_duty',
                        'field' => 'transiteo_duty_amount',
                        'value' => $transiteoDuty,
                        'base_value' => $baseTransiteoDuty,
                        'label' => __('Cross Border Duty'),
                    ]
                );
            }

//            $returnTotals = [];
//
//            //Set totals at the right place in the totals array (before grand total)
//            $memTotals = $this->_totals;
//            foreach ($memTotals as $key => $value) {
//                if ($key !== 'grand_total') {
//                    $returnTotals[$key] = $value;
//                } else {
//                    foreach ($totals as $tKey => $tValue) {
//                        $returnTotals[$tKey] = $tValue;
//                    }
//                    $returnTotals[$key] = $value;
//                }
//            }
//            $this->_totals = $returnTotals;

            foreach ($totals as $value) {
                $parent->addTotal($value, "shipping");
            }

//            $parent->addTotal($fee, 'fee');
//            // $this->_addTax('grand_total');
//            $parent->addTotal($fee, 'fee');
        }
        return $this;
    }
}
