<?php

declare(strict_types=1);

use PluginName\Settings\SettingsRegistry;
use PluginName\Settings\SettingsRestController;

use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    // Hook subscribers to register
    'hook_subscribers' => [],
    
    // Services with custom configuration can be defined here
    SettingsRestController::class => autowire()
        ->constructorParameter('registry', get(SettingsRegistry::class)),
];
