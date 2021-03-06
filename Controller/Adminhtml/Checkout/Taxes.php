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

namespace Transiteo\LandedCost\Controller\Checkout;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Json;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Transiteo\LandedCost\Service\TaxesService;

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
