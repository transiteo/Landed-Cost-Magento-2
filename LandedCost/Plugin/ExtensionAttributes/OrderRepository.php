<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

declare(strict_types=1);
namespace Transiteo\LandedCost\Plugin\ExtensionAttributes;

use Transiteo\LandedCost\Api\Data\TransiteoTaxesExtensionInterface;

/**
 *
 */
class OrderRepository
{

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderSearchResultInterface $searchResults
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     */
     public function afterGetList(\Magento\Sales\Api\OrderRepositoryInterface $subject,\Magento\Sales\Api\Data\OrderSearchResultInterface $searchResults){
         $entities = [];
         foreach ($searchResults->getItems() as $entity) {
             /**
              * @var TransiteoTaxesExtensionInterface $extensionAttributes
              */
             $extensionAttributes = $entity->getExtensionAttributes();
             $extensionAttributes->setBaseTransiteoDuty($entity->getData(TransiteoTaxesExtensionInterface::BASE_TRANSITEO_DUTY));
             $extensionAttributes->setTransiteoDuty($entity->getData(TransiteoTaxesExtensionInterface::TRANSITEO_DUTY));
             $extensionAttributes->setBaseTransiteoTotalTaxes($entity->getData(TransiteoTaxesExtensionInterface::BASE_TRANSITEO_TOTAL_TAXES));
             $extensionAttributes->setTransiteoTotalTaxes($entity->getData(TransiteoTaxesExtensionInterface::TRANSITEO_TOTAL_TAXES));
             $extensionAttributes->setBaseTransiteoSpecialTaxes($entity->getData(TransiteoTaxesExtensionInterface::BASE_TRANSITEO_SPECIAL_TAXES));
             $extensionAttributes->setTransiteoSpecialTaxes($entity->getData(TransiteoTaxesExtensionInterface::TRANSITEO_SPECIAL_TAXES));
             $extensionAttributes->setBaseTransiteoVat($entity->getData(TransiteoTaxesExtensionInterface::BASE_TRANSITEO_VAT));
             $extensionAttributes->setTransiteoVat($entity->getData(TransiteoTaxesExtensionInterface::TRANSITEO_VAT));
             $extensionAttributes->setTransiteoIncoterm($entity->getData(TransiteoTaxesExtensionInterface::TRANSITEO_INCOTERM));
             $entity->setExtensionAttributes($extensionAttributes);

             $entities[] = $entity;
         }
         $searchResults->setItems($entities);
         return $searchResults;
     }

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $entity
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function afterGet
    (
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $entity
    ) {
        /**
         * @var TransiteoTaxesExtensionInterface $extensionAttributes
         */
        $extensionAttributes = $entity->getExtensionAttributes();
        $extensionAttributes->setBaseTransiteoDuty($entity->getData(TransiteoTaxesExtensionInterface::BASE_TRANSITEO_DUTY));
        $extensionAttributes->setTransiteoDuty($entity->getData(TransiteoTaxesExtensionInterface::TRANSITEO_DUTY));
        $extensionAttributes->setBaseTransiteoTotalTaxes($entity->getData(TransiteoTaxesExtensionInterface::BASE_TRANSITEO_TOTAL_TAXES));
        $extensionAttributes->setTransiteoTotalTaxes($entity->getData(TransiteoTaxesExtensionInterface::TRANSITEO_TOTAL_TAXES));
        $extensionAttributes->setBaseTransiteoSpecialTaxes($entity->getData(TransiteoTaxesExtensionInterface::BASE_TRANSITEO_SPECIAL_TAXES));
        $extensionAttributes->setTransiteoSpecialTaxes($entity->getData(TransiteoTaxesExtensionInterface::TRANSITEO_SPECIAL_TAXES));
        $extensionAttributes->setBaseTransiteoVat($entity->getData(TransiteoTaxesExtensionInterface::BASE_TRANSITEO_VAT));
        $extensionAttributes->setTransiteoVat($entity->getData(TransiteoTaxesExtensionInterface::TRANSITEO_VAT));
        $extensionAttributes->setTransiteoIncoterm($entity->getData(TransiteoTaxesExtensionInterface::TRANSITEO_INCOTERM));
        $entity->setExtensionAttributes($extensionAttributes);

        return $entity;
    }

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $entity
     */
    public function beforeSave( \Magento\Sales\Api\OrderRepositoryInterface $subject, \Magento\Sales\Api\Data\OrderInterface $entity){
        $extensionAttributes = $entity->getExtensionAttributes();
        if(isset($extensionAttributes) && $extensionAttributes->getTransiteoTotalTaxes() === null){
            $this->afterGet($subject, $entity);
            $extensionAttributes = $entity->getExtensionAttributes();
        }
        if(isset($extensionAttributes)){
            /**
             * @var TransiteoTaxesExtensionInterface $extensionAttributes
             */
            $entity->setData(TransiteoTaxesExtensionInterface::BASE_TRANSITEO_DUTY, $extensionAttributes->getBaseTransiteoDuty());
            $entity->setData(TransiteoTaxesExtensionInterface::TRANSITEO_DUTY, $extensionAttributes->getTransiteoDuty());
            $entity->setData(TransiteoTaxesExtensionInterface::BASE_TRANSITEO_TOTAL_TAXES, $extensionAttributes->getBaseTransiteoTotalTaxes());
            $entity->setData(TransiteoTaxesExtensionInterface::TRANSITEO_TOTAL_TAXES, $extensionAttributes->getTransiteoTotalTaxes());
            $entity->setData(TransiteoTaxesExtensionInterface::BASE_TRANSITEO_SPECIAL_TAXES, $extensionAttributes->getBaseTransiteoSpecialTaxes());
            $entity->setData(TransiteoTaxesExtensionInterface::TRANSITEO_SPECIAL_TAXES, $extensionAttributes->getTransiteoSpecialTaxes());
            $entity->setData(TransiteoTaxesExtensionInterface::BASE_TRANSITEO_VAT, $extensionAttributes->getBaseTransiteoVat());
            $entity->setData(TransiteoTaxesExtensionInterface::TRANSITEO_VAT, $extensionAttributes->getTransiteoVat());
            $entity->setData(TransiteoTaxesExtensionInterface::TRANSITEO_INCOTERM, $extensionAttributes->getTransiteoIncoterm());
        }
    }

}
