<?php

declare(strict_types=1);

namespace ParcelTrap\DHL\Tests;

use ParcelTrap\DHL\DHLServiceProvider;
use ParcelTrap\ParcelTrapServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [ParcelTrapServiceProvider::class, DHLServiceProvider::class];
    }
}
