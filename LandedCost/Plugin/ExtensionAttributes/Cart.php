<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\LandedCost\Plugin\ExtensionAttributes;

use Transiteo\LandedCost\Api\Data\TransiteoTaxesExtensionInterface;

class Cart
{
    /**
     * @param \Magento\Quote\Api\Data\CartInterface $subject
     * @param \Magento\Quote\Api\Data\CartExtensionInterface|null $result
     * @return \Magento\Quote\Api\Data\CartExtensionInterface|null
     */
    public function afterGetExtensionAttributes(
        \Magento\Quote\Api\Data\CartInterface $subject,
        $extensionAttributes
    ){
        if(isset($extensionAttributes)){
            $extensionAttributes->setBaseTransiteoDuty($subject->getData(TransiteoTaxesExtensionInterface::BASE_TRANSITEO_DUTY));
            $extensionAttributes->setTransiteoDuty($subject->getData(TransiteoTaxesExtensionInterface::TRANSITEO_DUTY));
            $extensionAttributes->setBaseTransiteoTotalTaxes($subject->getData(TransiteoTaxesExtensionInterface::BASE_TRANSITEO_TOTAL_TAXES));
            $extensionAttributes->setTransiteoTotalTaxes($subject->getData(TransiteoTaxesExtensionInterface::TRANSITEO_TOTAL_TAXES));
            $extensionAttributes->setBaseTransiteoSpecialTaxes($subject->getData(TransiteoTaxesExtensionInterface::BASE_TRANSITEO_SPECIAL_TAXES));
            $extensionAttributes->setTransiteoSpecialTaxes($subject->getData(TransiteoTaxesExtensionInterface::TRANSITEO_SPECIAL_TAXES));
            $extensionAttributes->setBaseTransiteoVat($subject->getData(TransiteoTaxesExtensionInterface::BASE_TRANSITEO_VAT));
            $extensionAttributes->setTransiteoVat($subject->getData(TransiteoTaxesExtensionInterface::TRANSITEO_VAT));
            $extensionAttributes->setTransiteoIncoterm($subject->getData(TransiteoTaxesExtensionInterface::TRANSITEO_INCOTERM));
        }
        return $extensionAttributes;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $subject
     * @param \Magento\Quote\Api\Data\CartExtensionInterface $extensionAttributes
     */
    public function beforeSetExtensionAttributes(
        \Magento\Quote\Api\Data\CartInterface $subject,
        $extensionAttributes
    ){
        if(isset($extensionAttributes)){
            $subject->setData(TransiteoTaxesExtensionInterface::BASE_TRANSITEO_DUTY, $extensionAttributes->getBaseTransiteoDuty());
            $subject->setData(TransiteoTaxesExtensionInterface::TRANSITEO_DUTY, $extensionAttributes->getTransiteoDuty());
            $subject->setData(TransiteoTaxesExtensionInterface::BASE_TRANSITEO_TOTAL_TAXES, $extensionAttributes->getBaseTransiteoTotalTaxes());
            $subject->setData(TransiteoTaxesExtensionInterface::TRANSITEO_TOTAL_TAXES, $extensionAttributes->getTransiteoTotalTaxes());
            $subject->setData(TransiteoTaxesExtensionInterface::BASE_TRANSITEO_SPECIAL_TAXES, $extensionAttributes->getBaseTransiteoSpecialTaxes());
            $subject->setData(TransiteoTaxesExtensionInterface::TRANSITEO_SPECIAL_TAXES, $extensionAttributes->getTransiteoSpecialTaxes());
            $subject->setData(TransiteoTaxesExtensionInterface::BASE_TRANSITEO_VAT, $extensionAttributes->getBaseTransiteoVat());
            $subject->setData(TransiteoTaxesExtensionInterface::TRANSITEO_VAT, $extensionAttributes->getTransiteoVat());
            $subject->setData(TransiteoTaxesExtensionInterface::TRANSITEO_INCOTERM, $extensionAttributes->getTransiteoIncoterm());
        }
    }
}
