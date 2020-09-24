<?php
/**
 * @author Joris HART <jhart@efaktory.fr>
 * @copyright Copyright (c) 2020 eFaktory (https://www.efaktory.fr)
 * @link https://www.efaktory.fr
 */
declare(strict_types=1);

namespace Transiteo\Taxes\Pricing\Render;

use Magento\Framework\Pricing\Render\AbstractAdjustment;

class Adjustment extends AbstractAdjustment
{
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
