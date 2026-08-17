<?php

declare(strict_types=1);

namespace App\Providers;

use App\Interfaces\ColaboradorRepositoryInterface;
use App\Interfaces\JustificativaRepositoryInterface;
use App\Interfaces\PontoRepositoryInterface;
use App\Repositories\ColaboradorRepository;
use App\Repositories\JustificativaRepository;
use App\Repositories\PontoRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(PontoRepositoryInterface::class, PontoRepository::class);
        $this->app->bind(ColaboradorRepositoryInterface::class, ColaboradorRepository::class);
        $this->app->bind(JustificativaRepositoryInterface::class, JustificativaRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
