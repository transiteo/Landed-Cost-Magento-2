<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

namespace Transiteo\LandedCost\Api\Data;


interface TransiteoTaxesExtensionInterface extends \Magento\Framework\Api\ExtensionAttributesInterface, TransiteoItemTaxesExtensionInterface
{
    public const TRANSITEO_INCOTERM = "transiteo_incoterm";

    /**
     * @return string|null
     */
    public function getTransiteoIncoterm():?string;

    /**
     * @param string|null $incoterm
     * @return TransiteoTaxesExtensionInterface
     */
    public function setTransiteoIncoterm(?string $incoterm):TransiteoTaxesExtensionInterface;

}
