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
declare(strict_types=1);

namespace Transiteo\LandedCost\Model;

use Magento\Framework\Model\AbstractModel;
use Transiteo\LandedCost\Api\Data\DistrictInterface;
use Transiteo\LandedCost\Model\ResourceModel\District as DistrictResource;

class District extends AbstractModel implements DistrictInterface
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(DistrictResource::class);
    }

    /**
     * @inheritDoc
     */
    public function getIso(): ?string
    {
        return $this->_getData(DistrictInterface::ISO);
    }

    /**
     * @inheritDoc
     */
    public function getCountry(): ?string
    {
        return $this->_getData(DistrictInterface::COUNTRY);
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): ?string
    {
        return $this->_getData(DistrictInterface::LABEL);
    }

    /**
     * @inheritDoc
     */
    public function setIso(string $iso): DistrictInterface
    {
        $this->setData(DistrictInterface::ISO, $iso);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setCountry(string $country): DistrictInterface
    {
        $this->setData(DistrictInterface::COUNTRY, $country);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setLabel(string $label): DistrictInterface
    {
        $this->setData(DistrictInterface::LABEL, $label);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getState(): ?string
    {
        return $this->_getData(DistrictInterface::STATE);
    }

    /**
     * @inheritDoc
     */
    public function setState($state): DistrictInterface
    {
        $this->setData(DistrictInterface::STATE, $state);

        return $this;
    }
}
