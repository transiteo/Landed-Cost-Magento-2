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

namespace Transiteo\LandedCost\Model\Config\Backend\Serialized;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Config\Source\Order\Status;

class OrderStatusSerialized extends ArraySerialized
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory
     */
    protected $orderStatusCollectionFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $orderStatusCollectionFactory
     * @param array $data
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $orderStatusCollectionFactory,
        array $data = [],
        Json $serializer = null
    ) {
        $this->orderStatusCollectionFactory = $orderStatusCollectionFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data,
            $serializer);
    }



    protected function _afterLoad()
    {
        parent::_afterLoad();
        $value = $this->getValue();

        if($value === false){
            $value = [];
        }
        $value = $this->validateValues($value);
        $this->setValue($value);
    }

    /**
     * @param array $array
     * @return array
     */
    protected function validateValues(array $array):array
    {
        $statusList = array_column($array, 'magento_status');
        $statusCorrespondance = array_combine($statusList, array_column($array, 'transiteo_status'));

        $result = [];
        /**
         * @var \Magento\Sales\Model\ResourceModel\Order\Status\Collection $statusCollection
         */
        $statusCollection = $this->orderStatusCollectionFactory->create();
        $orderStatusList = array_column($statusCollection->toOptionArray(), 'value');
        foreach ($orderStatusList as $status) {
            if(!in_array($status, $statusList, true)){
                $result[$status] = ["magento_status" => $status, "transiteo_status" => \Transiteo\LandedCost\Model\Config::TRANSITEO_DEFAULT_STATUS];
            }else{
                $result[$status] = ["magento_status" => $status, "transiteo_status" => $statusCorrespondance[$status]];
            }
        }
        return array_values($result);
    }

    /**
     * @return OrderStatusSerialized
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $value = $this->validateValues($value);
        $this->setValue($value);
        return parent::beforeSave();
    }
}
