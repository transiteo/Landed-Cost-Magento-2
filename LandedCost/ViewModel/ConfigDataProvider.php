<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */
declare(strict_types=1);

namespace Transiteo\LandedCost\ViewModel;

use Transiteo\LandedCost\Model\Config;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ConfigDataProvider implements ArgumentInterface
{
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var SerializerInterface
     */
    protected $serializer;
    /**
     * @var array
     */
    protected $overrideConfig;
    /**
     * @var array
     */
    protected $settings;

    /**
     * @param Config $config
     * @param SerializerInterface $serializer
     * @param array $overrideConfig
     */
    public function __construct(
        Config $config,
        SerializerInterface $serializer,
        array $overrideConfig = []
    ) {
        $this->config = $config;
        $this->serializer = $serializer;
        $this->overrideConfig = $overrideConfig;
    }

    /**
     * Retrieve the default config settings
     *
     * @return array
     */
    public function getDefaultSettings(): array
    {
        if (!$this->settings) {
            $this->settings = array_merge([
                'enableLoader' => $this->config->isPDPPageLoaderEnabled(),
                'productFormSelector' => $this->config->getPDPProductFormSelector(),
                'qtyFieldSelector' => $this->config->getPDPQtyFieldSelector(),
                'totalTaxesPriceContainerSelector' => $this->config->getPDPTotalTaxesContainerSelector(),
                'vatPriceContainerSelector' => $this->config->getPDPVatContainerSelector(),
                'dutyPriceContainerSelector' => $this->config->getPDPDutyContainerSelector(),
                'specialTaxesPriceContainerSelector' => $this->config->getPDPSpecialTaxesContainerSelector(),
                'superAttributeSelector' => $this->config->getPDPSuperAttributeSelector(),
                'countrySelector' => $this->config->getPDPCountrySelector(),
                'eventAction' => $this->config->getPDPEventAction(),
                'delay' => $this->config->getPDPDelay(),
            ], $this->overrideConfig);
        }

        return $this->settings;
    }


    /**
     * Retrieve the final config settings
     *
     * @param array $config [optional]
     * @return array
     */
    public function getSettings(array $config = []): array
    {
        return array_merge($this->getDefaultSettings(), $config);
    }

    /**
     * Retrieve the config settings as json format
     *
     * @param array $config [optional]
     * @return string
     */
    public function getSettingsJson(array $config = []): string
    {
        return $this->serializer->serialize($this->getSettings($config));
    }

}
