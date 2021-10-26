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

namespace Transiteo\LandedCost\Logger;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Logger
 * @package Transiteo\LandedCost\Logger
 */
class QueueLogger extends \Monolog\Logger
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
            'transiteo_activation/duties/debug_mode',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
