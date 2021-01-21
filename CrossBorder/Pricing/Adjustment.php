<?php
/*
 * @author Blackbird Agency, Joris HART
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>, <jhart@efaktory.fr>
 */
declare(strict_types=1);

namespace Transiteo\CrossBorder\Pricing;

use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;
use Magento\Framework\Pricing\SaleableInterface;

class Adjustment implements AdjustmentInterface
{
    public const ADJUSTMENT_CODE = 'duties';

    public const ADJUSTMENT_VALUE = 0;

    public function getAdjustmentCode()
    {
        return self::ADJUSTMENT_CODE;
    }

    public function isIncludedInBasePrice()
    {
        return false;
    }

    public function isIncludedInDisplayPrice()
    {
        return true;
    }

    public function extractAdjustment($amount, SaleableInterface $saleableItem, $context = [])
    {
        return $amount - self::ADJUSTMENT_VALUE;
    }

    public function applyAdjustment($amount, SaleableInterface $saleableItem, $context = [])
    {
        return $amount + self::ADJUSTMENT_VALUE;
    }

    public function isExcludedWith($adjustmentCode)
    {
        return $this->getAdjustmentCode() === $adjustmentCode;
    }

    public function getSortOrder()
    {
        return 21;
    }
}
