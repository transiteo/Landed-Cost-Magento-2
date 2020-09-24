<?php
/**
 * @author Joris HART <jhart@efaktory.fr>
 * @copyright Copyright (c) 2020 eFaktory (https://www.efaktory.fr)
 * @link https://www.efaktory.fr
 */
declare(strict_types=1);

namespace Transiteo\Taxes\Pricing;

use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;
use Magento\Framework\Pricing\SaleableInterface;

class Adjustment implements AdjustmentInterface
{
    const ADJUSTMENT_CODE = 'duties';

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
        return false;
    }

    public function extractAdjustment($amount, SaleableInterface $saleableItem, $context = [])
    {
        return 0;
    }

    public function applyAdjustment($amount, SaleableInterface $saleableItem, $context = [])
    {
        return $amount;
    }

    public function isExcludedWith($adjustmentCode)
    {
        return true;
    }

    public function getSortOrder()
    {
        return 10;
    }
}
