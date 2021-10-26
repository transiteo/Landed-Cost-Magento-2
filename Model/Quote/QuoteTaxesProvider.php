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

namespace Transiteo\LandedCost\Model\Quote;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;

class QuoteTaxesProvider implements ConfigProviderInterface
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        Session $checkoutSession,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $additionalVariables = [];
        $quote = $this->checkoutSession->getQuote();
        if ($quote->getTransiteoDisplay()) {
            $duty = $quote->getTransiteoDuty();
            $vat = $this->checkoutSession->getQuote()->getTransiteoVat();
            $specialTaxes = $this->checkoutSession->getQuote()->getTransiteoSpecialTaxes();
            $totalTaxes = $this->checkoutSession->getQuote()->getTransiteoTotalTaxes();
            $incoterm = $this->checkoutSession->getQuote()->getTransiteoIncoterm();
            $additionalVariables['quote_id'] = $this->checkoutSession->getQuoteId();
            $additionalVariables['transiteo_duty'] = $duty;
            $additionalVariables['transiteo_vat'] = $vat;
            $additionalVariables['transiteo_special_taxes'] = $specialTaxes;
            $additionalVariables['transiteo_total_taxes'] = $totalTaxes;
            $additionalVariables['transiteo_incoterm'] = $incoterm;
            $additionalVariables['transiteo_checkout_taxes_url'] = $this->urlBuilder->getBaseUrl() . 'transiteo/checkout/taxes';
        }
        return $additionalVariables;
    }
}
