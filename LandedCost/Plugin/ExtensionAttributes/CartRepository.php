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
class CartRepository
{

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $subject
     * @param \Magento\Quote\Api\Data\CartSearchResultInterface $searchResults
     * @return \Magento\Quote\Api\Data\CartSearchResultInterface
     */
     public function afterGetList(\Magento\Quote\Api\CartRepositoryInterface $subject,\Magento\Quote\Api\Data\CartSearchResultInterface $searchResults){
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
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param \Magento\Catalog\Api\Data\ProductInterface $entity
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function afterGet
    (
        \Magento\Quote\Api\CartRepositoryInterface $subject,
        \Magento\Quote\Api\Data\CartInterface $entity
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
     * @param \Magento\Quote\Api\CartRepositoryInterface $subject
     * @param \Magento\Quote\Api\Data\CartInterface $entity
     */
    public function beforeSave( \Magento\Quote\Api\CartRepositoryInterface $subject, \Magento\Quote\Api\Data\CartInterface $entity){
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
