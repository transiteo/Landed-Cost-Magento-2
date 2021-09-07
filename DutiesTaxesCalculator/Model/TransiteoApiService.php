<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

declare(strict_types=1);

namespace Transiteo\DutiesTaxesCalculator\Model;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\FlagManager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Transiteo\DutiesTaxesCalculator\Controller\Cookie;
use Transiteo\DutiesTaxesCalculator\Logger\Logger;

/**


 * Class TransiteoApiService
 */
class TransiteoApiService
{
    /**
     * API request URL
     */
    public const API_REQUEST_URI = 'https://api.transiteo.io/';

    /**
    * API AUTH request URL
    */
    public const API_AUTH_REQUEST_URI = 'https://auth.transiteo.io/';

    /**
     * API request endpoint
     */
    public const API_REQUEST_ENDPOINT = 'https://api.transiteo.io/';

    public const COOKIE_NAME = 'transiteo-id-token';
    public const ID_TOKEN_FLAG_NAME = 'transiteo-id-token';
    public const ACCESS_TOKEN_FLAG_NAME = 'transiteo-access-token';
    public const TOKEN_EXPIRES_IN_FLAG_NAME = 'transiteo-id-token-expires';
    public const TOKEN_RECEIVED_TIMESTAMP = 'transiteo-token-received-timestamp';
    public const TOKEN_TYPE_FLAG_NAME = 'transiteo-id-token-type';
    public const TOKEN_VALIDITY_MARGIN_IN_SECONDS = 60;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    private $cookie;

    private $idToken;

    protected $scopeConfig;

    protected $flagManager;
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * TransiteoApiService constructor
     *
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        SerializerInterface $serializer,
        Cookie $cookie,
        ScopeConfigInterface $scopeConfig,
        FlagManager $flagManager,
        Logger $logger
    ) {
        $this->logger = $logger;
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->serializer = $serializer;
        $this->cookie = $cookie;
        $this->scopeConfig = $scopeConfig;
        $this->flagManager = $flagManager;
    }

    /**
     * Get Id Token to use for auth in next requests
     */
    public function getIdToken()
    {
        $clientId = $this->scopeConfig->getValue('transiteo_activation/general/client_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $refreshToken = $this->scopeConfig->getValue('transiteo_activation/general/refresh_token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $response = $this->doRequest(
            self::API_AUTH_REQUEST_URI . "oauth2/token",
            [
                'headers' => [
                    'Content-type'     => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'client_id' => $clientId,
                    'refresh_token' => $refreshToken
                ],
                'http_errors' => false
            ],
            Request::HTTP_METHOD_POST
        );

        $status = $response->getStatusCode(); // 200 status code
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents(); // here you will have the API response in JSON format

        if ($status == 200) {
            $responseArray = $this->serializer->unserialize($responseContent);

            $this->idToken  = $responseArray['id_token'];
            //$this->cookie->set(self::COOKIE_NAME, $this->idToken, 3500);
            $this->flagManager->saveFlag(self::ID_TOKEN_FLAG_NAME, $this->idToken);
            $accessToken    = $responseArray['access_token'];
            $this->flagManager->saveFlag(self::ACCESS_TOKEN_FLAG_NAME, $accessToken);
            $expires_in     = $responseArray['expires_in'];
            $this->flagManager->saveFlag(self::TOKEN_EXPIRES_IN_FLAG_NAME, $expires_in);
            $token_type     = $responseArray['token_type'];
            $this->flagManager->saveFlag(self::TOKEN_TYPE_FLAG_NAME, $token_type);
            $date = new \DateTime();
            $this->flagManager->saveFlag(self::TOKEN_RECEIVED_TIMESTAMP, $date->getTimestamp());
        }

        return $responseContent;
    }

    /**
     * Verify if token has expired
     *
     * @return bool
     */
    public function hasTokenExpired()
    {
        $tokenTimestamp = $this->flagManager->getFlagData(self::TOKEN_RECEIVED_TIMESTAMP);
        if ($tokenTimestamp !== null) {
            $date = (new \DateTime())->getTimestamp();
            $validity = $this->flagManager->getFlagData(self::TOKEN_EXPIRES_IN_FLAG_NAME);
            return $date > $tokenTimestamp + $validity - self::TOKEN_VALIDITY_MARGIN_IN_SECONDS;
        }
        return true;
    }

    /**
     * Get Duties for a designated product
     */
    public function getDuties($productsParams)
    {
        $this->refreshIdToken();

        $request = [
            'headers' => [
                'Content-type'     => 'application/json',
                'Authorization' => $this->idToken,
            ],
            'json' => $productsParams
        ];

        //////////////////LOGGER//////////////
        ob_start();
        var_dump($request);
        $result = ob_get_clean();
        $this->logger->debug("Request : " . $result);
        ///////////////////////////////////////

        $response = $this->doRequest(
            self::API_REQUEST_URI . "v1/taxsrv/dutyCalculation",
            $request,
            Request::HTTP_METHOD_POST
        );

        $status = $response->getStatusCode();

        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents();

        $responseArray = json_decode($responseContent);

        ///LOGGER///
        $this->logger->debug('Response : status => ' . ($status ?? 'null') . ' message : ' . $response->getReasonPhrase());

        if ($status = "200") {
            if (isset($responseArray)) {
                ////LOGGER////
                ob_start();
                var_dump($responseArray);
                $result = ob_get_clean();
                $this->logger->debug('Response Content : ' . $result);
            }
        } else {
            if (array_key_exists('message', $responseArray)) {
                ////LOGGER////
                $this->logger->debug('Response : status => ' . $status . ' message : ' . $responseArray['message']);
            }
        }

        if ($status == "401") {
            if (isset($responseArray->message) && $responseArray->message == "The incoming token has expired") {
                $this->refreshIdToken(true);
                $this->getDuties($productsParams);
            }
        }

        return $responseContent;
    }

    /**
     * Get Duties for a designated product
     */
    public function getCurrencyRate($currencyFrom, $currencyTo, $timeout, $amount = 1)
    {
        $this->refreshIdToken(true);

        $response = $this->doRequest(
            self::API_REQUEST_URI . "v1/data/currency?amount=1&toCurrency=" . $currencyTo . "&fromCurrency=" . $currencyFrom,
            [
                'headers' => [
                    'Content-type'     => 'application/json',
                    'Authorization' => $this->idToken,
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
            $responseArray = json_decode($responseContent);
            if (isset($responseArray->message) && $responseArray->message == "The incoming token has expired") {
                $this->refreshIdToken(true);
                $this->getCurrencyRate($currencyFrom, $currencyTo, $timeout, $amount);
            }
        }

        return $responseContent;
    }

    private function refreshIdToken($forceRefresh = false)
    {
        $idTokenStored = $this->flagManager->getFlagData(self::ID_TOKEN_FLAG_NAME);

        if (($this->idToken == null &&  $idTokenStored == null) || $forceRefresh || $this->hasTokenExpired()) {
            $this->getIdToken();
        } elseif ($this->idToken == null && $idTokenStored != null) {
            $this->idToken = $idTokenStored;
        }
    }

    /**
     * Do API request with provided params
     *
     * @param string $uriEndpoint
     * @param array $params
     * @param string $requestMethod
     *
     * @return Response
     */
    private function doRequest(
        string $uriEndpoint,
        array $params = [],
        string $requestMethod = Request::HTTP_METHOD_GET
    ): Response {
        /** @var Client $client */
        $client = $this->clientFactory->create();
        try {
            $response = $client->request(
                $requestMethod,
                $uriEndpoint,
                $params
            );
        } catch (GuzzleException $exception) {
            /** @var Response $response */
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getResponse()->getBody()->getContents()
            ]);
        }

        return $response;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
