<?php
/**
 * @author Joris HART <jhart@efaktory.fr>
 * @copyright Copyright (c) 2020 eFaktory (https://www.efaktory.fr)
 * @link https://www.efaktory.fr
 */

namespace Transiteo\CrossBorder\Api\Data;

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