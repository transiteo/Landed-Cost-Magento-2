<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

declare(strict_types=1);
namespace Transiteo\LandedCost\Plugin\ExtensionAttributes;

use Transiteo\LandedCost\Api\Data\TransiteoItemTaxesExtensionInterface;
/**
 *
 */
class InvoiceItemRepository
{
    /**
     * @param \Magento\Sales\Api\InvoiceItemRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\InvoiceItemSearchResultInterface $searchResults
     * @return \Magento\Sales\Api\Data\InvoiceItemSearchResultInterface
     */
     public function afterGetList(\Magento\Sales\Api\InvoiceItemRepositoryInterface $subject,\Magento\Sales\Api\Data\InvoiceItemSearchResultInterface $searchResults){
         $entities = [];
         foreach ($searchResults->getItems() as $entity) {
             /**
              * @var TransiteoItemTaxesExtensionInterface $extensionAttributes
              */
             $extensionAttributes = $entity->getExtensionAttributes();
             $extensionAttributes->setBaseTransiteoDuty($entity->getData(TransiteoItemTaxesExtensionInterface::BASE_TRANSITEO_DUTY));
             $extensionAttributes->setTransiteoDuty($entity->getData(TransiteoItemTaxesExtensionInterface::TRANSITEO_DUTY));
             $extensionAttributes->setBaseTransiteoTotalTaxes($entity->getData(TransiteoItemTaxesExtensionInterface::BASE_TRANSITEO_TOTAL_TAXES));
             $extensionAttributes->setTransiteoTotalTaxes($entity->getData(TransiteoItemTaxesExtensionInterface::TRANSITEO_TOTAL_TAXES));
             $extensionAttributes->setBaseTransiteoSpecialTaxes($entity->getData(TransiteoItemTaxesExtensionInterface::BASE_TRANSITEO_SPECIAL_TAXES));
             $extensionAttributes->setTransiteoSpecialTaxes($entity->getData(TransiteoItemTaxesExtensionInterface::TRANSITEO_SPECIAL_TAXES));
             $extensionAttributes->setBaseTransiteoVat($entity->getData(TransiteoItemTaxesExtensionInterface::BASE_TRANSITEO_VAT));
             $extensionAttributes->setTransiteoVat($entity->getData(TransiteoItemTaxesExtensionInterface::TRANSITEO_VAT));
             $entity->setExtensionAttributes($extensionAttributes);

             $entities[] = $entity;
         }
         $searchResults->setItems($entities);
         return $searchResults;
     }

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param \Magento\Catalog\Api\Data\ProductInterface $entity
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function afterGet
    (
        \Magento\Sales\Api\InvoiceItemRepositoryInterface $subject,
        \Magento\Sales\Api\Data\InvoiceItemInterface $entity
    ) {
        /**
         * @var TransiteoItemTaxesExtensionInterface $extensionAttributes
         */
        $extensionAttributes = $entity->getExtensionAttributes();

        $extensionAttributes->setBaseTransiteoDuty($entity->getData(TransiteoItemTaxesExtensionInterface::BASE_TRANSITEO_DUTY));
        $extensionAttributes->setTransiteoDuty($entity->getData(TransiteoItemTaxesExtensionInterface::TRANSITEO_DUTY));
        $extensionAttributes->setBaseTransiteoTotalTaxes($entity->getData(TransiteoItemTaxesExtensionInterface::BASE_TRANSITEO_TOTAL_TAXES));
        $extensionAttributes->setTransiteoTotalTaxes($entity->getData(TransiteoItemTaxesExtensionInterface::TRANSITEO_TOTAL_TAXES));
        $extensionAttributes->setBaseTransiteoSpecialTaxes($entity->getData(TransiteoItemTaxesExtensionInterface::BASE_TRANSITEO_SPECIAL_TAXES));
        $extensionAttributes->setTransiteoSpecialTaxes($entity->getData(TransiteoItemTaxesExtensionInterface::TRANSITEO_SPECIAL_TAXES));
        $extensionAttributes->setBaseTransiteoVat($entity->getData(TransiteoItemTaxesExtensionInterface::BASE_TRANSITEO_VAT));
        $extensionAttributes->setTransiteoVat($entity->getData(TransiteoItemTaxesExtensionInterface::TRANSITEO_VAT));
        $entity->setExtensionAttributes($extensionAttributes);

        return $entity;
    }

    /**
     * @param \Magento\Sales\Api\InvoiceItemRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\InvoiceItemInterface $entity
     */
    public function beforeSave( \Magento\Sales\Api\InvoiceItemRepositoryInterface $subject, \Magento\Sales\Api\Data\InvoiceItemInterface $entity){
        $extensionAttributes = $entity->getExtensionAttributes();
        if(isset($extensionAttributes) && $extensionAttributes->getTransiteoTotalTaxes() === null){
            $this->afterGet($subject, $entity);
            $extensionAttributes = $entity->getExtensionAttributes();
        }
        if(isset($extensionAttributes)){
            /**
             * @var TransiteoItemTaxesExtensionInterface $extensionAttributes
             */
            $entity->setData(TransiteoItemTaxesExtensionInterface::BASE_TRANSITEO_DUTY, $extensionAttributes->getBaseTransiteoDuty());
            $entity->setData(TransiteoItemTaxesExtensionInterface::TRANSITEO_DUTY, $extensionAttributes->getTransiteoDuty());
            $entity->setData(TransiteoItemTaxesExtensionInterface::BASE_TRANSITEO_TOTAL_TAXES, $extensionAttributes->getBaseTransiteoTotalTaxes());
            $entity->setData(TransiteoItemTaxesExtensionInterface::TRANSITEO_TOTAL_TAXES, $extensionAttributes->getTransiteoTotalTaxes());
            $entity->setData(TransiteoItemTaxesExtensionInterface::BASE_TRANSITEO_SPECIAL_TAXES, $extensionAttributes->getBaseTransiteoSpecialTaxes());
            $entity->setData(TransiteoItemTaxesExtensionInterface::TRANSITEO_SPECIAL_TAXES, $extensionAttributes->getTransiteoSpecialTaxes());
            $entity->setData(TransiteoItemTaxesExtensionInterface::BASE_TRANSITEO_VAT, $extensionAttributes->getBaseTransiteoVat());
            $entity->setData(TransiteoItemTaxesExtensionInterface::TRANSITEO_VAT, $extensionAttributes->getTransiteoVat());
        }
    }

}
