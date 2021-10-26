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

namespace Transiteo\LandedCost\Controller\District;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Transiteo\LandedCost\Api\Data\DistrictInterface;
use Transiteo\LandedCost\Model\DistrictRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Index extends Action
{
    protected $searchCriteriaBuilder;

    protected $districtRepository;

    public function __construct(
        Context $context,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DistrictRepository $districtRepository
    ) {
        parent::__construct($context);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->districtRepository    = $districtRepository;
    }

    public function execute()
    {

        $country_id = $this->getRequest()->getParam('country');

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(DistrictInterface::COUNTRY, $country_id)
            ->create();

        $searchResult = $this->districtRepository->getList($searchCriteria);

        /** @var Json $jsonResult */
        $jsonResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $items = [];
        foreach ($searchResult->getItems() as $item) {
            $items[] = $item->getData();
        }

        $jsonResult->setData([
            'items' => $items
        ]);

        return $jsonResult;
    }

}
