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

namespace Transiteo\LandedCost\Plugin\ExtensionAttributes;

use Transiteo\LandedCost\Api\Data\TransiteoTaxesExtensionInterface;

class Order
{
    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $subject
     * @param \Magento\Sales\Api\Data\OrderExtensionInterface|null $result
     * @return \Magento\Sales\Api\Data\OrderExtensionInterface|null
     */
    public function afterGetExtensionAttributes(
        \Magento\Sales\Api\Data\OrderInterface $subject,
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
     * @param \Magento\Sales\Api\Data\OrderInterface $subject
     * @param \Magento\Sales\Api\Data\OrderExtensionInterface $extensionAttributes
     */
    public function beforeSetExtensionAttributes(
        \Magento\Sales\Api\Data\OrderInterface $subject,
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
