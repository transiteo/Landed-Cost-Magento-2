<?php
/**
 * Created by Ny Ando.
 * solofoniando@gmail.com
 */

declare(strict_types=1);

namespace Transiteo\LandedCost\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Transiteo\LandedCost\Model\TransiteoApiService;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Webapi\Rest\Request;

/**
 * Class CalculateMarketplaceTaxesResolver
 * @package Transiteo\LandedCost\Model\Resolver
 */
class CalculateMarketplaceTaxesResolver implements ResolverInterface
{
    /**
     * CalculateMarketplaceTaxesResolver constructor.
     * @param TransiteoApiService $apiService
     * @param CustomerSession $customerSession
     */
    public function __construct(private TransiteoApiService $apiService, private CustomerSession $customerSession)
    {}

    /**
     * @param $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     * @throws GraphQlAuthenticationException
     */
    public function resolve(
        $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array {

        if (!$this->customerSession->isLoggedIn()) {
            throw new GraphQlAuthenticationException(__("Unauthorized."));
        }

        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }

        try {
            $params = $args['input'];
            $request = [
                'headers' => [
                    'Content-type'     => 'application/json',
                    'Authorization' => $this->apiService->getIdToken(),
                ],
                'json' => $params
            ];

            $response = $this->apiService->doRequest(
                TransiteoApiService::API_REQUEST_URI . 'v1/taxsrv/dutyCalculation',
                $request,
                Request::HTTP_METHOD_POST
            );

            $status = $response->getStatusCode();
            $responseBody = $response->getBody();
            $responseContent = $responseBody->getContents();

            $responseArray = \json_decode($responseContent);

            if ($status == "200") {
                if (isset($responseArray)) {
                    $result = \json_encode($responseArray);
                    $this->apiService->getLogger()->debug('Response Content : ' . $result);
                }
            } else {
                if (is_array($responseArray) && array_key_exists('message', $responseArray)) {
                    $message = $responseArray['message'];
                } else {
                    $message = $response->getReasonPhrase();
                }
                $this->apiService->getLogger()->debug('Response : status => ' . $status . ' message : ' . $message);

                throw new \Exception($message);
            }
            $responseArray->timestamp = (new \DateTime())->getTimestamp();

            return (array)$responseArray;
        } catch (\Exception $e) {
            throw new GraphQlInputException(__('Unable to calculate duties and taxes: ' . $e->getMessage()));
        }
    }
}
