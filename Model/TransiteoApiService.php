<?php

declare(strict_types=1);

namespace Transiteo\Taxes\Model;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use MageMastery\FirstModule\Controller\Cookie;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata;
use Magento\Framework\App\Config\ScopeConfigInterface;
/**


 * Class TransiteoApiService
 */
class TransiteoApiService
{
    /**
     * API request URL
     */
    const API_REQUEST_URI = 'https://api.dev.transiteo.io/';

     /**
     * API AUTH request URL
     */
    const API_AUTH_REQUEST_URI = 'https://auth.dev.transiteo.io/';

    /**
     * API request endpoint
     */
    const API_REQUEST_ENDPOINT = '';

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
        ScopeConfigInterface $scopeConfig
    ) {
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->serializer = $serializer;
        $this->cookie = $cookie;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get Id Token to use for auth in next requests
     */
    public function getIdToken()
    {   

        $clientId = $this->scopeConfig->getValue('transiteo_activation/general/client_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $refreshToken = $this->scopeConfig->getValue('transiteo_activation/general/refresh_token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        $response = $this->doRequest(
            self::API_AUTH_REQUEST_URI."oauth2/token", 
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
            ]
            , Request::HTTP_METHOD_POST
        );

        $status = $response->getStatusCode(); // 200 status code
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents(); // here you will have the API response in JSON format

        if($status == 200){
            $responseArray = $this->serializer->unserialize($responseContent);

            $this->idToken  = $responseArray['id_token'];
            $this->cookie->set($this->idToken, 3500);

            $accessToken    = $responseArray['access_token'];
            $expires_in     = $responseArray['expires_in'];
            $token_type     = $responseArray['token_type'];
        }
        
        return $responseContent;
    }


    /**
     * Get Duties for a designated product
     */
    public function getDuties($productsParams)
    {   

        $jsonExample = file_get_contents(__DIR__."/../example2.json");
        $productsParams = $this->serializer->unserialize($jsonExample);

        if($this->idToken == null && $this->cookie->get() == null)
            $this->getIdToken();
        elseif($this->idToken == null && $this->cookie->get() != null)
            $this->idToken = $this->cookie->get();

        

        $response = $this->doRequest(
            self::API_REQUEST_URI."v1/taxsrv/dutyCalculation", 
            [
                'headers' => [
                    'Content-type'     => 'application/json',
                    'Authorization' => $this->idToken,
                ],
                'json' => $productsParams,
                'http_errors' => false
            ]
            , Request::HTTP_METHOD_POST
        );
        
        $status = $response->getStatusCode(); 

        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents();
        
        return $responseContent;
    }

    /**
     * Get Duties for a designated product
     */
    public function getCurrencyRate($currencyFrom, $currencyTo, $amount = 1)
    {   

        if($this->idToken == null && $this->cookie->get() == null)
            $this->getIdToken();
        elseif($this->idToken == null && $this->cookie->get() != null)
            $this->idToken = $this->cookie->get();

        $response = $this->doRequest(
            self::API_REQUEST_URI."v1/data/currency", 
            [
                'headers' => [
                    'Content-type'     => 'application/json',
                    'Authorization' => $this->idToken,
                ],
                'json' => [
                    'amount' => 1,
                    'toCurrency' => $currencyTo,
                    'fromCurrency' => $currencyFrom
                ],
                'http_errors' => false
                
            ]
            , Request::HTTP_METHOD_POST
        );
        
        $status = $response->getStatusCode(); 

        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents();
        
        echo $status;
        var_dump($responseContent);
        die();

        return $responseContent;
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
                'reason' => $exception->getMessage()
            ]);
        }

        return $response;
    }
}
