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
        Cookie $cookie
    ) {
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->serializer = $serializer;
        $this->cookie = $cookie;
    }

    /**
     * Get Id Token to use for auth in next requests
     */
    public function getIdToken()
    {
        $response = $this->doRequest(
            self::API_AUTH_REQUEST_URI."oauth2/token", 
            [   
                'headers' => [
                    'Content-type'     => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'grant_type' => 'refresh_token', 
                    'refresh_token' => 'eyJjdHkiOiJKV1QiLCJlbmMiOiJBMjU2R0NNIiwiYWxnIjoiUlNBLU9BRVAifQ.F_BG5zHSUKLtYBnUE86iKVuekDZ5hnvtjfx0Eb2Bqp0_8KCW0_TCosi4PKFKSISsEevxk-2KAznRCnu66jhzxYZc9J41umjluGsDkb5eS0AFrg8uh8_qCpbldlpptUsvwmYxc7KzcJwtFNR-Eh33txi5e9tDb7llqGjFBhtgf4aUiUMlYtiXjgfNqJPX1KFABjCKZ4-qGX2mADIBC2hRthe2Tf5oxC8hMZCQ3kZewiZ69T3WAgJE_JZK88NyG6Yads4aznhMOFT23H-MgG1EPQkE3Lm-jqx3kwWYx7tSOo-hbbyth-KkNGSo0qaQSXooATerXTkaaMTYZitmPJ0sEw.1g8yumc4eme3foD7.KHoIG3uIwp8MhWysdoptmjC6DiUvG4A7RvBHbVnxhIQBdtZ9a6YA_KB3IUQZ91xY1Phtd59ZVOg4_mU0nDH2vvuWWTwdcaee4NKtIRokj1i1pjaCeU32_AT8GUZ1VI2gJeWCHXP1u5HtYMKFjSKZulZ-TGxGDBA2qUX_oLitNm54BnhwkCTS7AsS7Tz-Uwe0g8zKMSuMnATELTm6EoogOgSc6M6SMT3OH0Yf59MkE0B9SbB3Uzpw9JT0WvKw32x7UbWlUOOSOw3IuQjahCZ7rxnrS3wQ6dpisqer2tPOxoEO8F65d7I3GdajLfaeJmGmzMr82BeJTP4PGVW9SVjYFCQ8KR0rGwBNgzVR4XYXyiyW2M5-DmcCMJQ4JMEirFF9yPiNdOCch_zPNCC3aS4ukgR6Yq4y36A2QSLnozWzguiu2t68ddKnK8F5sHpcQNSw3ZXTvmk3RcKE1YuhUpDOxP4Bul2Kj_W2rVCADkhvZ_WwUvcG3ogxqtuOV0FauOZ1VqYb64u-ToAAvg-AkfcHDBfIU8GXRi2rseuqAksfx2gTdOdiQK6VLNCMh941RIldrPazBq-rDwdIXZcYygJsLm7x8fF0OAF6VRJQ52xKN7St3ola87azOjG1mJ4wKK1Yg4aVsyQwLgC2_F-jhjwXqFISBGxOgTP3taoflIQPPpY3Fhzb5hz1-LycOAxAToAJt7H7bMhimZonecYrgbMm4hu3cyI6RxcCCTX7vY6K5iPzjryldVl_IKv9gDtTmPYAnIGbIpAvNlM7IiE5_imTyI_DStle8-hRgKDAlZ_E_rk4tT5SgFI1_ZfCa24TryMGR07mBTyj1uyprYMTZZdmLpOhT2zEh_aGufGAr-SUzwhYMGNccr6B8toXcji8Jfk2clH7TVUlVaKr5plb7WZMBpStydM8hfQHONQhhnSdkDwkwfHtg1urG5Xz_wF5HDqIj1kCZN9GKP6Zsk2_MGIelN7OMqOR8jDD_W2bgfA4NjMVLIBwZNQoxXJZ76LP_F6M0fEmq5u76ETa0LLehl7L4DcMFpwSySXSaa2atboxWKNnupiPtawNZe9ssX7HHJwAjMX9NLd9B8wXglu2SJ722wK6nAqT2h6XrGnnPeRO7x2YJ9CC4Ie-RedrIxKvcX1ExZnW6Zgngzlnh74QdGCCIA8tKqSu_rAyyG12Sh0yGN-fRfej4XuTIoMVajlfBIeYz1zKHtRo_u2DUhiydD9ihLIG8733GUEXAYMEPuJxbndlot1hdut2jiPygpVGi5uzfvB_cgAzhwUIoMxWU8Tl4TTgHdfnuag87NEkMrkdRU7zmv0xxCihG_QlNFw.PXqjksFteBwt17Zcg7AVFg'  
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
        echo $status." - ".$responseContent;
        die();
        return $responseContent;
    }


    /**
     * Get Duties for a designated product
     */
    public function getDuties($productsParams)
    {   
        $jsonExample = file_get_contents(__DIR__."/../example.json");
        $productsParams = $this->serializer->unserialize($jsonExample);

        if($this->idToken == null && $this->cookie->get() == null)
            $this->getIdToken();
        elseif($this->idToken == null && $this->cookie->get() != null)
            $this->idToken = $this->cookie->get();

        $response = $this->doRequest(
            self::API_REQUEST_URI."v1/taxsrv/dutyCalculation", 
            [
                'headers' => [
                    'content-type'     => 'application/json',
                    'Authorization' => $this->idToken,
                ],
                'json' => $this->serializer->serialize($productsParams)
            ]
            , Request::HTTP_METHOD_POST
        );

        $status = $response->getStatusCode(); 

        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents();
        
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
