<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\LandedCost\Block\Order;

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
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_source = $parent->getSource();

        $salesEntity = $parent->getOrder();
        $creditMemo = $parent->getCreditmemo();
        $invoice = $parent->getInvoice();
        $isCreditMemo = false;
        $isInvoice = false;
        if(isset($creditMemo)){
            $isCreditMemo = true;
            $salesEntity = $creditMemo;
        }elseif (isset($invoice)){
            $isInvoice = true;
            $salesEntity = $invoice;
        }

        $transiteoTotalTaxes = $salesEntity->getTransiteoTotalTaxes();
        $baseTransiteoTotalTaxes = $salesEntity->getBaseTransiteoTotalTaxes();

        $incoterm = $salesEntity->getTransiteoIncoterm();
        if ($transiteoTotalTaxes && (!$isCreditMemo || $incoterm==="ddp")) {
            $totals = [];
            $transiteoDuty = $salesEntity->getTransiteoDuty();
            $baseTransiteoDuty = $salesEntity->getBaseTransiteoDuty();
            $transiteoVat = $salesEntity->getTransiteoVat();
            $baseTransiteoVat = $salesEntity->getBaseTransiteoVat();
            $transiteoSpecialTaxes = $salesEntity->getTransiteoSpecialTaxes();
            $baseTransiteoSpecialTaxes = $salesEntity->getBaseTransiteoSpecialTaxes();
            if ($incoterm === "ddp") {
                $included = ' (' . __('included').')';
                if($isCreditMemo || $isInvoice){
                    $included = '';
                }
            } else {
                $included = ' (' . __('not included').')';
            }

            $totalSetted = 0;
            if (isset($transiteoVat)) {
                $totalSetted++;
            }
            if (isset($transiteoDuty)) {
                $totalSetted++;
            }
            if (isset($transiteoSpecialTaxes)) {
                $totalSetted++;
            }

            if (($totalSetted > 1) && !$isCreditMemo && ! $isInvoice) {
                $totals['transiteo_total_taxes'] = new \Magento\Framework\DataObject(
                    [
                        'code' => 'transiteo_total_taxes',
                        'field' => 'transiteo_total_taxes_amount',
                        'strong' => true,
                        'value' => $transiteoTotalTaxes,
                        'base_value' => $baseTransiteoTotalTaxes,
                        'label' => __('Duty & Taxes Total')  . ' ' . $included,
                    ]
                );
            }

            if (isset($transiteoSpecialTaxes)) {
                $totals['transiteo_special_taxes'] = new \Magento\Framework\DataObject(
                    [
                        'code' => 'transiteo_special_taxes',
                        'field' => 'transiteo_special_taxes_amount',
                        'value' => $transiteoSpecialTaxes,
                        'base_value' => $baseTransiteoSpecialTaxes,
                        'label' => __('Special Taxes SubTotal') . ' ' . $included,
                    ]
                );
            }

            if (isset($transiteoDuty)) {
                $totals['transiteo_duty'] = new \Magento\Framework\DataObject(
                    [
                        'code' => 'transiteo_duty',
                        'field' => 'transiteo_duty_amount',
                        'value' => $transiteoDuty,
                        'base_value' => $baseTransiteoDuty,
                        'label' => __('Duty SubTotal'). ' ' . $included,
                    ]
                );
            }

            if (isset($transiteoVat)) {
                $totals['transiteo_vat'] = new \Magento\Framework\DataObject(
                    [
                        'code' => 'transiteo_vat',
                        'field' => 'transiteo_vat_amount',
                        'value' => $transiteoVat,
                        'base_value' => $baseTransiteoVat,
                        'label' => __('VAT/GST SubTotal') . ' ' . $included,
                    ]
                );
            }
            $subtotalBefore = "shipping";
            if($isCreditMemo || $isInvoice){
                $subtotalBefore = "shipping";
            }

            foreach ($totals as $value) {
                $parent->addTotal($value, $subtotalBefore);
            }
        }
        return $this;
    }
}
