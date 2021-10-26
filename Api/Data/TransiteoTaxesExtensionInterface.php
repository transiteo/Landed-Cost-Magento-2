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
