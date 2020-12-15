<?php

namespace Transiteo\Taxes\Model\Quote;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;

class QuoteTaxesProvider implements ConfigProviderInterface
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    public function __construct(
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $quote = $this->checkoutSession->getQuote();
        $total = $quote->getTotals()['transiteo-duty-taxes'];
        //////////////////LOGGER//////////////
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($total->getTransiteoDuty());
        ///////////////////////////////////////
        $duty = $quote->getTransiteoDuty();
        $vat = $this->checkoutSession->getQuote()->getTransiteoVat();
        $specialTaxes = $this->checkoutSession->getQuote()->getTransiteoSpecialTaxes();
        $totalTaxes = $this->checkoutSession->getQuote()->getTransiteoTotalTaxes();
        $additionalVariables['transiteo_duty'] = $duty;
        $additionalVariables['transiteo_vat'] = $vat;
        $additionalVariables['transiteo_special_taxes'] = $specialTaxes;
        $additionalVariables['transiteo_total_taxes'] = $totalTaxes;
        return $additionalVariables;
    }
}
