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

namespace Transiteo\LandedCost\Api\Data;


interface TransiteoItemTaxesExtensionInterface extends \Magento\Framework\Api\ExtensionAttributesInterface
{
    public const BASE_TRANSITEO_TOTAL_TAXES = "base_transiteo_total_taxes";
    public const TRANSITEO_TOTAL_TAXES = "transiteo_total_taxes";
    public const BASE_TRANSITEO_VAT = "base_transiteo_vat";
    public const TRANSITEO_VAT = "transiteo_vat";
    public const BASE_TRANSITEO_SPECIAL_TAXES = "base_transiteo_special_taxes";
    public const TRANSITEO_SPECIAL_TAXES = "transiteo_special_taxes";
    public const BASE_TRANSITEO_DUTY = "base_transiteo_duty";
    public const TRANSITEO_DUTY = "transiteo_duty";

    /**
     * @return float|null
     */
    public function getBaseTransiteoTotalTaxes():?float;

    /**
     * @param float|null $totalTaxes
     * @return TransiteoTaxesExtensionInterface
     */
    public function setBaseTransiteoTotalTaxes(?float $totalTaxes):TransiteoTaxesExtensionInterface;


    /**
     * @return float|null
     */
    public function getBaseTransiteoSpecialTaxes():?float;

    /**
     * @param float|null $specialTaxes
     * @return TransiteoTaxesExtensionInterface
     */
    public function setBaseTransiteoSpecialTaxes(?float $specialTaxes):TransiteoTaxesExtensionInterface;

    /**
     * @return float|null
     */
    public function getBaseTransiteoVat():?float;

    /**
     * @param float|null $vat
     * @return TransiteoTaxesExtensionInterface
     */
    public function setBaseTransiteoVat(?float $vat):TransiteoTaxesExtensionInterface;

    /**
     * @return float|null
     */
    public function getBaseTransiteoDuty():?float;

    /**
     * @param float|null $duty
     * @return TransiteoTaxesExtensionInterface
     */
    public function setBaseTransiteoDuty(?float $duty):TransiteoTaxesExtensionInterface;

    /**
     * @return float|null
     */
    public function getTransiteoTotalTaxes():?float;

    /**
     * @param float|null $totalTaxes
     * @return TransiteoTaxesExtensionInterface
     */
    public function setTransiteoTotalTaxes(?float $totalTaxes):TransiteoTaxesExtensionInterface;


    /**
     * @return float|null
     */
    public function getTransiteoSpecialTaxes():?float;

    /**
     * @param float|null $specialTaxes
     * @return TransiteoTaxesExtensionInterface
     */
    public function setTransiteoSpecialTaxes(?float $specialTaxes):TransiteoTaxesExtensionInterface;

    /**
     * @return float|null
     */
    public function getTransiteoVat():?float;

    /**
     * @param float|null $vat
     * @return TransiteoTaxesExtensionInterface
     */
    public function setTransiteoVat(?float $vat):TransiteoTaxesExtensionInterface;

    /**
     * @return float|null
     */
    public function getTransiteoDuty():?float;

    /**
     * @param float|null $duty
     * @return TransiteoTaxesExtensionInterface
     */
    public function setTransiteoDuty(?float $duty):TransiteoTaxesExtensionInterface;

}
