<?php

declare(strict_types=1);

namespace ParcelTrap\DHL;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;
use ParcelTrap\Contracts\Factory;

class DHLServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->make(Factory::class)->extend(DHL::IDENTIFIER, function () {
            return new DHL(
                clientId: (string) $this->app->make(Repository::class)->get('parceltrap.dhl.client_id'),
            );
        });
    }
}
