<?php

declare(strict_types=1);

namespace ParcelTrap\DHL;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;
use ParcelTrap\Contracts\Factory;
use ParcelTrap\ParcelTrap;

class DHLServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var ParcelTrap $factory */
        $factory = $this->app->make(Factory::class);

        $factory->extend(DHL::IDENTIFIER, function () {
            /** @var Repository $config */
            $config = $this->app->make(Repository::class);

            return new DHL(
                /** @phpstan-ignore-next-line */
                clientId: (string) $config->get('parceltrap.drivers.dhl.client_id'),
            );
        });
    }
}
