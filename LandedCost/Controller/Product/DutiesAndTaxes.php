<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

declare(strict_types=1);

namespace Transiteo\LandedCost\Controller\Product;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Transiteo\LandedCost\Service\TaxesService;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DataObjectFactory;

class DutiesAndTaxes implements ActionInterface
{

    private const CACHE_CONTROL_DATE_FORMAT = 'D, d M Y H:i:s T';
    private const CACHE_CONTROL_TTL = 8600;

    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var ResultFactory
     */
    protected $resultFactory;
    /**
     * @var DateTime
     */
    protected $dateTime;
    /**
     * @var Validator
     */
    protected $formKeyValidator;
    /**
     * @var RedirectInterface
     */
    protected $redirect;
    /**
     * @var TaxesService
     */
    protected $taxesServices;
    /**
     * @var Configurable
     */
    protected $configurableType;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @param RequestInterface $request
     * @param ResultFactory $resultFactory
     * @param DateTime $dateTime
     * @param Validator $formKeyValidator
     * @param RedirectInterface $redirect
     * @param TaxesService $taxesService
     * @param Configurable $configurableType
     * @param ProductRepositoryInterface $productRepository
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        RequestInterface $request,
        ResultFactory $resultFactory,
        DateTime $dateTime,
        Validator $formKeyValidator,
        RedirectInterface $redirect,
        TaxesService $taxesService,
        Configurable $configurableType,
        ProductRepositoryInterface $productRepository,
        DataObjectFactory $dataObjectFactory
    )
    {
        $this->redirect = $redirect;
        $this->dateTime = $dateTime;
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->taxesServices = $taxesService;
        $this->configurableType = $configurableType;
        $this->productRepository = $productRepository;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            /** @var Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('noroute');

            return $resultForward;
        }

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            /** @var Json $resultJson */
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData(['backUrl' => $this->redirect->getRefererUrl()]);

            return $resultJson;
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $country = $this->getRequest()->getParam('country_code');
            $countryCookie = $this->taxesServices->getToCountryFromCookie();
            if(!isset($countryCookie)){
                $this->taxesServices->updateCookieValue($country);
            }elseif($country !== "undefined"&& $country!== "" && $country !== $countryCookie->getCountryId()){
                $this->taxesServices->updateCookieValue($country);
            }
            $qty = $this->getRequest()->getParam('qty');
            if(isset($qty)){
                $sku = $this->getProductSku($this->getRequest()->getParams());
                $country = $this->taxesServices->getToCountryFromCookie();
                $duties = $this->taxesServices->getDutiesByProductSku($sku, (float) $qty);
                $duties['sku'] = $sku;
                $resultJson->setHeader('pragma', 'cache', true);
                $resultJson->setHeader(
                    'cache-control',
                    'public, max-age=' . self::CACHE_CONTROL_TTL . ', -maxage=' . self::CACHE_CONTROL_TTL,
                    true
                );
                $resultJson->setHeader(
                    'expires',
                    $this->dateTime->gmtDate(
                        self::CACHE_CONTROL_DATE_FORMAT,
                        $this->dateTime->gmtTimestamp() + self::CACHE_CONTROL_TTL
                    ),
                    true
                );
            }
            $duties['country'] = $country->getName();
            $duties['country_code'] = $country->getCountryId();
            $duties['success'] = true;
            $resultJson->setData($duties);
        }catch (Exception $e){
            $resultJson->setData(['error' => $e->getMessage()]);
        }

        return $resultJson;
    }

    /*
     * If the current is configurable, get the simple product associated to the configurable
     *
     * @param array $params
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getProductSku(array $params): string
    {
        $product = $this->productRepository->getById($params['product']);

        if ($product->getTypeId() === 'simple') {
            return $this->taxesServices->getConfig()->getTransiteoProductSku($product);
        }

        //Transform request array param to an object
        $request = $this->dataObjectFactory->create();
        $request->setData($params);

        //Get the simple product using request param from the configurable
        $configurableProducts = $this->configurableType->processConfiguration($request, $product);
        if(isset($configurableProducts) && !empty($configurableProducts)){
            $product = end($configurableProducts);
        }

        return $this->taxesServices->getConfig()->getTransiteoProductSku($product);
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }


}

