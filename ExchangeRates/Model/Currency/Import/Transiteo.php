<?php

namespace Transiteo\ExchangeRates\Model\Currency\Import;

use Transiteo\Base\Model\TransiteoApiService;

/**
 * Currency rate import model (From https://frankfurter.app/)
 */
class Transiteo extends \Magento\Directory\Model\Currency\Import\AbstractImport
{
    /**
     * @var string
     */
    const CURRENCY_CONVERTER_URL = 'https://api.frankfurter.app/current?from={{CURRENCY_FROM}}&to={{CURRENCY_TO}}&amount=1';

    /** @var \Magento\Framework\Json\Helper\Data */
    protected $jsonHelper;

    /**
     * Http Client Factory
     *
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $httpClientFactory;

    /**
     * Core scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Transiteo Api Service
     *
     * @var Transiteo\Base\Model\TransiteoApiService
     */
    private $apiService;


    /**
     * Initialize dependencies
     *
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        TransiteoApiService $apiService,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        parent::__construct($currencyFactory);
        $this->scopeConfig = $scopeConfig;
        $this->httpClientFactory = $httpClientFactory;
        $this->jsonHelper = $jsonHelper;
        $this->apiService= $apiService;
    }

    /**
     * @param string $currencyFrom
     * @param string $currencyTo
     * @param int $retry
     * @return float|null
     */
    protected function _convert($currencyFrom, $currencyTo, $retry = 0)
    {   
        $result = null;
        $timeout = (int)$this->scopeConfig->getValue(
            'currency/transiteo/timeout',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        try {
            
            $response = $this->apiService->getCurrencyRate($currencyFrom, $currencyTo, $timeout);

            $data = $this->jsonHelper->jsonDecode($response);

            if (isset($data['result'])) {
                $result = (float) $data['result'];
            } else {
                $this->_messages[] = __('We can\'t retrieve a rate from Transiteo.');
            }
        } catch (\Exception $e) {
            if ($retry == 0) {
                $this->_convert($currencyFrom, $currencyTo, 1);
            } else {
                $this->_messages[] = __('We can\'t retrieve a rate from Transiteo.');
            }
        }
        return $result;
    }
}