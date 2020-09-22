<?php
/**
 * @author Joris HART <jhart@efaktory.fr>
 * @copyright Copyright (c) 2020 eFaktory (https://www.efaktory.fr)
 * @link https://www.efaktory.fr
 */
declare(strict_types=1);

namespace Transiteo\Taxes\Model;

use Magento\Framework\Model\AbstractModel;
use Transiteo\Taxes\Api\Data\DistrictInterface;
use Transiteo\Taxes\Model\ResourceModel\District as DistrictResource;

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
