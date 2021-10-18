<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

declare(strict_types=1);

namespace Transiteo\LandedCost\Model;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\FlagManager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Transiteo\LandedCost\Controller\Cookie;
use Transiteo\LandedCost\Logger\Logger;

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
    protected $responseFactory;

    /**
     * @var ClientFactory
     */
    protected $clientFactory;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    protected $idToken;

    protected $flagManager;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var Config
     */
    protected $config;

    /**
     * TransiteoApiService constructor
     *
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param SerializerInterface $serializer
     * @param Config $config
     * @param FlagManager $flagManager
     * @param Logger $logger
     */
    public function __construct(
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        SerializerInterface $serializer,
        \Transiteo\LandedCost\Model\Config $config,
        FlagManager $flagManager,
        Logger $logger
    ) {
        $this->logger = $logger;
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->serializer = $serializer;
        $this->config = $config;
        $this->flagManager = $flagManager;
    }

    /**
     * Get Id Token to use for auth in next requests
     */
    protected function getIdTokenFromApi()
    {
        $clientId = $this->config->getTransiteoClientId();
        $refreshToken = $this->config->getTransiteoRefreshToken();

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

        return $this->idToken;
    }

    /**
     * Verify if token has expired
     *
     * @return bool
     */
    protected function hasTokenExpired()
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
     * Refresh the Id Token
     */
    public function refreshIdToken()
    {
        $this->idToken =  $this->getIdTokenFromApi();
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
    public function doRequest(
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
     * @return mixed
     */
    public function getIdToken()
    {
        $idTokenStored = $this->flagManager->getFlagData(self::ID_TOKEN_FLAG_NAME);
        if (($this->idToken === null &&  $idTokenStored === null) || $this->hasTokenExpired()) {
            $this->refreshIdToken();
        } elseif ($this->idToken === null && $idTokenStored !== null) {
            $this->idToken = $idTokenStored;
        }
        return $this->idToken;
    }

    /**
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }
}
