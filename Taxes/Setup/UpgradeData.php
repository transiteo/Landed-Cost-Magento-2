<?php

namespace Transiteo\Taxes\Setup;

use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{

    /**
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * @var QuoteSetupFactory
     */
    protected $quoteSetupFactory;

    /**
     * Constructor
     *
     * @param \Magento\Sales\Setup\SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        QuoteSetupFactory $quoteSetupFactory
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
    }

    /**
     * @inheritDoc
     */
    public function upgrade(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), "1.0.1", "<")) {
            $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
            $salesSetup->addAttribute(
                'order',
                'transiteo_total_taxes',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'scale' => 4,
                    'precision' => 20,
                    'unsigned' => false,
                    'nullable' =>true,
                    'grid' => true
                ]
            );
            $salesSetup->addAttribute(
                'order',
                'transiteo_duty',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'scale' => 4,
                    'precision' => 20,
                    'unsigned' => false,
                    'nullable' =>true,
                    'grid' => true
                ]
            );
            $salesSetup->addAttribute(
                'order',
                'transiteo_vat',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'scale' => 4,
                    'precision' => 20,
                    'unsigned' => false,
                    'nullable' =>true,
                    'grid' => true
                ]
            );
            $salesSetup->addAttribute(
                'order',
                'transiteo_special_taxes',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'scale' => 4,
                    'precision' => 20,
                    'unsigned' => false,
                    'nullable' =>true,
                    'grid' => true
                ]
            );

            $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
            $quoteSetup->addAttribute(
                'order',
                'transiteo_total_taxes',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'scale' => 4,
                    'precision' => 20,
                    'unsigned' => false,
                    'nullable' =>true
                ]
            );
            $quoteSetup->addAttribute(
                'order',
                'transiteo_duty',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'scale' => 4,
                    'precision' => 20,
                    'unsigned' => false,
                    'nullable' =>true
                ]
            );
            $quoteSetup->addAttribute(
                'order',
                'transiteo_vat',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'scale' => 4,
                    'precision' => 20,
                    'unsigned' => false,
                    'nullable' =>true
                ]
            );
            $quoteSetup->addAttribute(
                'order',
                'transiteo_special_taxes',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'scale' => 4,
                    'precision' => 20,
                    'unsigned' => false,
                    'nullable' =>true
                ]
            );
        }
    }
}
