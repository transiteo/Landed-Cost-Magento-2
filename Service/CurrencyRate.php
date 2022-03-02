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

declare(strict_types=1);

namespace Transiteo\LandedCost\Service;

use Magento\Framework\Webapi\Rest\Request;
use Transiteo\LandedCost\Model\TransiteoApiService;
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
        $this->transiteoApiService->getLogger()->debug("Getting Currency rate");

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
        $this->transiteoApiService->getLogger()->debug($responseContent);

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
