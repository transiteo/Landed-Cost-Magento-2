<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

declare(strict_types=1);

namespace Transiteo\DutiesTaxesCalculator\Service;

use Magento\Framework\Webapi\Rest\Request;
use Magento\Tests\NamingConvention\true\string;
use Transiteo\DutiesTaxesCalculator\Model\TransiteoApiService;
class CurrencyRate
{

    /**
     * @var TransiteoApiService
     */
    protected $transiteoApiService;

    /**
     * @param TransiteoApiService $transiteoApiService
     */
    public function __construct(
        TransiteoApiService $transiteoApiService
    )
    {
        $this->transiteoApiService = $transiteoApiService;
    }

    /**
     * Get Currency rate
     */
    public function getCurrencyRate($currencyFrom, $currencyTo, $timeout, $amount = 1):string
    {
        $response = $this->transiteoApiService->doRequest(
            TransiteoApiService::API_REQUEST_URI . "v1/data/currency?amount=1&toCurrency=" . $currencyTo . "&fromCurrency=" . $currencyFrom,
            [
                'headers' => [
                    'Content-type'     => 'application/json',
                    'Authorization' => $this->transiteoApiService->getIdToken(),
                ],
                'timeout' => $timeout,
                'http_errors' => false
            ],
            Request::HTTP_METHOD_GET
        );

        $status = $response->getStatusCode();

        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents();

        if ($status == "401") {
            $responseArray = \json_decode($responseContent);
            if (isset($responseArray->message) && $responseArray->message === "The incoming token has expired") {
                $this->transiteoApiService->refreshIdToken();
                $this->getCurrencyRate($currencyFrom, $currencyTo, $timeout, $amount);
            }
        }

        return $responseContent;
    }
}
