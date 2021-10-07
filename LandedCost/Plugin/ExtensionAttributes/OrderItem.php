<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\LandedCost\Plugin\ExtensionAttributes;

use Transiteo\LandedCost\Api\Data\TransiteoItemTaxesExtensionInterface;

class OrderItem
{
    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $subject
     * @param \Magento\Sales\Api\Data\OrderItemExtensionInterface|null $result
     * @return \Magento\Sales\Api\Data\OrderItemExtensionInterface|null
     */
    public function afterGetExtensionAttributes(
        \Magento\Sales\Api\Data\OrderItemInterface $subject,
        $extensionAttributes
    ){
        if(isset($extensionAttributes)){
            $extensionAttributes->setBaseTransiteoDuty($subject->getData(TransiteoItemTaxesExtensionInterface::BASE_TRANSITEO_DUTY));
            $extensionAttributes->setTransiteoDuty($subject->getData(TransiteoItemTaxesExtensionInterface::TRANSITEO_DUTY));
            $extensionAttributes->setBaseTransiteoTotalTaxes($subject->getData(TransiteoItemTaxesExtensionInterface::BASE_TRANSITEO_TOTAL_TAXES));
            $extensionAttributes->setTransiteoTotalTaxes($subject->getData(TransiteoItemTaxesExtensionInterface::TRANSITEO_TOTAL_TAXES));
            $extensionAttributes->setBaseTransiteoSpecialTaxes($subject->getData(TransiteoItemTaxesExtensionInterface::BASE_TRANSITEO_SPECIAL_TAXES));
            $extensionAttributes->setTransiteoSpecialTaxes($subject->getData(TransiteoItemTaxesExtensionInterface::TRANSITEO_SPECIAL_TAXES));
            $extensionAttributes->setBaseTransiteoVat($subject->getData(TransiteoItemTaxesExtensionInterface::BASE_TRANSITEO_VAT));
            $extensionAttributes->setTransiteoVat($subject->getData(TransiteoItemTaxesExtensionInterface::TRANSITEO_VAT));
        }
        return $extensionAttributes;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $subject
     * @param \Magento\Sales\Api\Data\OrderItemExtensionInterface $extensionAttributes
     */
    public function beforeSetExtensionAttributes(
        \Magento\Sales\Api\Data\OrderItemInterface $subject,
        $extensionAttributes
    ){
        if(isset($extensionAttributes)){
            $subject->setData(TransiteoItemTaxesExtensionInterface::BASE_TRANSITEO_DUTY, $extensionAttributes->getBaseTransiteoDuty());
            $subject->setData(TransiteoItemTaxesExtensionInterface::TRANSITEO_DUTY, $extensionAttributes->getTransiteoDuty());
            $subject->setData(TransiteoItemTaxesExtensionInterface::BASE_TRANSITEO_TOTAL_TAXES, $extensionAttributes->getBaseTransiteoTotalTaxes());
            $subject->setData(TransiteoItemTaxesExtensionInterface::TRANSITEO_TOTAL_TAXES, $extensionAttributes->getTransiteoTotalTaxes());
            $subject->setData(TransiteoItemTaxesExtensionInterface::BASE_TRANSITEO_SPECIAL_TAXES, $extensionAttributes->getBaseTransiteoSpecialTaxes());
            $subject->setData(TransiteoItemTaxesExtensionInterface::TRANSITEO_SPECIAL_TAXES, $extensionAttributes->getTransiteoSpecialTaxes());
            $subject->setData(TransiteoItemTaxesExtensionInterface::BASE_TRANSITEO_VAT, $extensionAttributes->getBaseTransiteoVat());
            $subject->setData(TransiteoItemTaxesExtensionInterface::TRANSITEO_VAT, $extensionAttributes->getTransiteoVat());
        }
    }
}
