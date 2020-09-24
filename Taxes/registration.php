<?php

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Transiteo_Taxes',
    __DIR__
);

require_once(__DIR__.'/lib/internal/MaxMind-DB-Reader-php-master/autoload.php');
