<?php
/**
 * @author Joris HART <jhart@efaktory.fr>
 * @copyright Copyright (c) 2020 eFaktory (https://www.efaktory.fr)
 * @link https://www.efaktory.fr
 */
declare(strict_types=1);

namespace Transiteo\Taxes\Controller\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Transiteo\Taxes\Service\TaxesService;

class Taxes extends Action
{
    protected $taxesService;

    public function __construct(
        Context $context,
        TaxesService $taxesService
    ) {
        parent::__construct($context);

        $this->taxesService = $taxesService;
    }

    public function execute()
    {
        /** @var $response Json */
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $productId = $this->getRequest()->getParam('id');

        try {
            $duties = $this->taxesService->getDutiesByProductId((int) $productId);
        } catch (NoSuchEntityException $e) {

            $response->setData([
                'error' => true,
                'message' => $e->getMessage(),
                'data' => []
            ]);

            return $response;

        } catch (\Exception $e) {
            $response->setData([
                'error' => true,
                'message' => $e->getMessage(),
                'data' => []
            ]);

            return $response;
        }

        $response->setData($duties);

        return $response;
    }
}
