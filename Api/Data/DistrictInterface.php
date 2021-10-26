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

interface DistrictInterface
{
    const ID = 'entity_id';
    const ISO = 'iso';
    const COUNTRY = 'country_id';
    const STATE = 'state';
    const LABEL = 'label';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getIso();

    /**
     * @return string
     */
    public function getCountry();

    /**
     * @return string
     */
    public function getState();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param int $id
     *
     * @return DistrictInterface
     */
    public function setId(int $id);

    /**
     * @param string $iso
     *
     * @return DistrictInterface
     */
    public function setIso(string $iso);

    /**
     * @param string $country
     *
     * @return DistrictInterface
     */
    public function setCountry(string $country);

    /**
     * @param string $state
     *
     * @return DistrictInterface
     */
    public function setState($state);

    /**
     * @param string $label
     *
     * @return DistrictInterface
     */
    public function setLabel(string $label);
}
