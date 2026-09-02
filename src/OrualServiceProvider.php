<?php

declare(strict_types=1);

namespace Sifrious\Orual;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;
use Sifrious\Orual\Review\ReviewReadModel;
use Sifrious\Orual\Review\ReviewStore;
use Sifrious\Orual\Review\SqlReviewReadModel;
use Sifrious\Orual\Review\SqlReviewStore;

class OrualServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/orual.php', 'orual');

        $this->app->singleton(ReviewStore::class, fn ($app): ReviewStore => new SqlReviewStore(
            $this->connection($app),
        ));

        $this->app->singleton(ReviewReadModel::class, fn ($app): ReviewReadModel => new SqlReviewReadModel(
            $this->connection($app),
        ));
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/orual.php' => $this->app->configPath('orual.php'),
            ], 'orual-config');
        }
    }

    private function connection(Application $app): ConnectionInterface
    {
        return $app->make(DatabaseManager::class)->connection(config('orual.connection'));
    }
}
