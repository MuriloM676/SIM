<?php

namespace App\Providers;

use App\Repositories\Contracts\MultaRepositoryInterface;
use App\Repositories\Eloquent\MultaRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind de Repositories
        $this->app->bind(MultaRepositoryInterface::class, MultaRepository::class);
        
        // Adicionar outros repositories conforme necessário
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
