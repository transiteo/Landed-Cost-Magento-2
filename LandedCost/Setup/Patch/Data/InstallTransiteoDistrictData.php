<?php
/*
 * @copyright Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Transiteo\LandedCost\Setup\Patch\Data;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\File\Csv;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\SampleData\FixtureManager;
use Transiteo\LandedCost\Api\Data\DistrictInterface;
use Transiteo\LandedCost\Api\Data\DistrictInterfaceFactory;
use Transiteo\LandedCost\Api\DistrictRepositoryInterface;

class InstallTransiteoDistrictData implements DataPatchInterface
{
    /**
     * @var DistrictInterfaceFactory
     */
    protected $districtFactory;

    /**
     * @var DistrictRepositoryInterface
     */
    protected $districtRepository;

    /**
     * @var FixtureManager
     */
    protected $fixtureManager;

    /**
     * @var Csv
     */
    protected $csvReader;

    /**
     * InstallTransiteoDistrictData constructor.
     *
     * @param DistrictInterfaceFactory $districtFactory
     * @param DistrictRepositoryInterface $districtRepository
     * @param FixtureManager $fixtureManager
     * @param Csv $csvReader
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
        $fileName = $this->fixtureManager->getFixture('Transiteo_LandedCost::fixtures/districts.csv');
        if (! file_exists($fileName)) {
            return;
        }
        $this->districtRepository->deleteAllDistricts();

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

        if (count($isoParts) < 2) {
            return null;
        }

        return $isoParts[1];
    }
}
