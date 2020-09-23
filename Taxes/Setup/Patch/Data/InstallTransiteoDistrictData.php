<?php
/**
 * @author Joris HART <jhart@efaktory.fr>
 * @copyright Copyright (c) 2020 eFaktory (https://www.efaktory.fr)
 * @link https://www.efaktory.fr
 */
declare(strict_types=1);

namespace Transiteo\Taxes\Setup\Patch\Data;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\SampleData\FixtureManager;
use Transiteo\Taxes\Api\Data\DistrictInterface;
use Transiteo\Taxes\Api\Data\DistrictInterfaceFactory;
use Transiteo\Taxes\Api\DistrictRepositoryInterface;
use Magento\Framework\File\Csv;

class InstallTransiteoDistrictData implements DataPatchInterface
{
    /**
     * @var DistrictInterfaceFactory
     */
    private $districtFactory;

    /**
     * @var DistrictRepositoryInterface
     */
    private $districtRepository;

    /**
     * @var FixtureManager
     */
    private $fixtureManager;

    /**
     * @var Csv
     */
    protected $csvReader;

    /**
     * InstallTransiteoDistrictData constructor.
     *
     * @param DistrictInterfaceFactory $districtFactory
     * @param DistrictRepositoryInterface $districtRepository
     */
    public function __construct(
        DistrictInterfaceFactory $districtFactory,
        DistrictRepositoryInterface $districtRepository,
        FixtureManager $fixtureManager,
        Csv $csvReader
    ) {
        $this->districtFactory    = $districtFactory;
        $this->districtRepository = $districtRepository;
        $this->fixtureManager     = $fixtureManager;
        $this->csvReader          = $csvReader;
    }

    public function apply()
    {
        $fileName = $this->fixtureManager->getFixture('Transiteo_Taxes::fixtures/districts.csv');

        if ( ! file_exists($fileName)) {
            return;
        }

        $rows = $this->csvReader->getData($fileName);
        $header = array_shift($rows);

        foreach ($rows as $key => $row) {
            /** @var DistrictInterface $district */
            $district = $this->districtFactory->create();

            $countryId = $this->extractCountryId($row[0]);
            $state = $this->extractStateId($row[0]);

            $district->setCountry($countryId);
            $district->setLabel($row[2]);
            $district->setIso($row[0]);
            $district->setState($state);

            try {
                $this->districtRepository->save($district);
            } catch (CouldNotSaveException $e) {
                echo $e->getMessage();
                die();
                continue;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @param string $iso
     *
     * @return string|null
     */
    private function extractCountryId(string $iso): ?string
    {
        $isoParts = explode('-', $iso);

        return $isoParts[0];
    }

    /**
     * @param string $iso
     *
     * @return string|null
     */
    private function extractStateId(string $iso): ?string
    {
        $isoParts = explode('-', $iso);

        if (count($isoParts) < 3) {
            return null;
        }

        return $isoParts[1];
    }
}
