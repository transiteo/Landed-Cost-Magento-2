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

namespace Transiteo\LandedCost\Pricing\Render;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Render\AbstractAdjustment;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Locale\Format;

class Adjustment extends AbstractAdjustment
{
    /**
     * @var \Magento\Framework\Locale\Format
     */
    private $localeFormat;

    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    public function __construct(
        Template\Context $context,
        PriceCurrencyInterface $priceCurrency,
        JsonSerializer $jsonSerializer,
        Format $localeFormat,
        array $data = []
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->localeFormat   = $localeFormat;

        parent::__construct($context, $priceCurrency, $data);
    }

    /**
     * Composes configuration for js price format
     *
     * @return string
     */
    public function getPriceFormatJson()
    {
        return $this->jsonSerializer->serialize($this->localeFormat->getPriceFormat());
    }

    protected function apply()
    {
        return $this->toHtml();
    }

    /**
     * Obtain code of adjustment type
     *
     * @return string
     */
    public function getAdjustmentCode()
    {
        return \Magento\Tax\Pricing\Adjustment::ADJUSTMENT_CODE;
    }

    public function isEnabled()
    {
        return true;
    }
}
