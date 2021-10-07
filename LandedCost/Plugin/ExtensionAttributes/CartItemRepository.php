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
class CartItemRepository
{
    /**
     * @param \Magento\Quote\Api\CartItemRepositoryInterface $subject
     * @param \Magento\Quote\Api\Data\CartItemInterface[] $searchResults
     * @return \Magento\Quote\Api\Data\CartItemInterface[]
     */
     public function afterGetList(\Magento\Quote\Api\CartItemRepositoryInterface $subject,array $searchResults){
         $entities = [];
         foreach ($searchResults as $entity) {
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
         return $entities;
     }

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param \Magento\Catalog\Api\Data\ProductInterface $entity
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function afterGet
    (
        \Magento\Quote\Api\CartItemRepositoryInterface $subject,
        \Magento\Quote\Api\Data\CartItemInterface $entity
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
     * @param \Magento\Quote\Api\CartItemRepositoryInterface $subject
     * @param \Magento\Quote\Api\Data\CartItemInterface $entity
     */
    public function beforeSave( \Magento\Quote\Api\CartItemRepositoryInterface $subject, \Magento\Quote\Api\Data\CartItemInterface $entity){
        $extensionAttributes = $entity->getExtensionAttributes();
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
