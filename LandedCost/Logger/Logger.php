<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\LandedCost\Logger;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Logger
 * @package Transiteo\LandedCost\Logger
 */
class Logger extends \Monolog\Logger
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct($name, array $handlers = [], array $processors = [], ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($name, $handlers, $processors);
    }

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function debug($message, array $context = [])
    {
        if ($this->isLoggingActive()) {
            return parent::debug($message, $context);
        }

        return true;
    }

    /**
     * @return mixed
     */
    protected function isLoggingActive()
    {
        return $this->scopeConfig->getValue(
            'transiteo_settings/duties/debug_mode',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
