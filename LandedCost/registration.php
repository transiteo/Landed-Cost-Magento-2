<?php
/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Transiteo_LandedCost',
    __DIR__
);

require_once __DIR__ . '/lib/internal/MaxMind-DB-Reader-php-master/autoload.php';
