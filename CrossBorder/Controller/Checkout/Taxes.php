<?php
/**
 * @author Joris HART <jhart@efaktory.fr>
 * @copyright Copyright (c) 2020 eFaktory (https://www.efaktory.fr)
 * @link https://www.efaktory.fr
 */
declare(strict_types=1);

namespace Transiteo\CrossBorder\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Transiteo\CrossBorder\Service\TaxesService;

class Taxes extends Action
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    protected $quoteRepository;

    /**
     * @var TaxesService $taxesService
     */
    protected $taxesService;

    public function __construct(
        Context $context,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        TaxesService $taxesService
    ) {
        parent::__construct($context);
        $this->quoteRepository = $quoteRepository;
        $this->taxesService = $taxesService;
    }

    public function execute()
    {
        /** @var $response Json */
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $quoteId = $this->getRequest()->getParam('quote', null);
        $requestVal = $this->getRequest()->getParam('request', null);
        $values = [];
        try {
            $quote = $this->quoteRepository->get($quoteId);
            if ($requestVal === null) {
                $values['transiteo_vat'] = $quote->getTransiteoVat();
                $values['transiteo_duty'] = $quote->getTransiteoDuty();
                $values['transiteo_special_taxes'] = $quote->getTransiteoSpecialTaxes();
                $values['transiteo_total_taxes'] = $quote->getTransiteoTotalTaxes();
            } else {
                if ($requestVal === 'transiteo_vat') {
                    $values['value'] = $quote->getTransiteoVat();
                }
                if ($requestVal === 'transiteo_duty') {
                    $values['value'] = $quote->getTransiteoDuty();
                }
                if ($requestVal === 'transiteo_special_taxes') {
                    $values['value'] = $quote->getTransiteoSpecialTaxes();
                }
                if ($requestVal === 'transiteo_total_taxes') {
                    $values['value'] = $quote->getTransiteoTotalTaxes();
                }
            }
            $values['transiteo_incoterm'] =  $quote->getTransiteoIncoterm();
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

        $response->setData($values);

        return $response;
    }
}
