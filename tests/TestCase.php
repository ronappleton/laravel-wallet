<?php

declare(strict_types=1);

namespace Tests;

use Appleton\LaravelWallet\Models\Concerns\HasWallets;
use Appleton\LaravelWallet\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function createOwner(int $count = 1): Model|Collection
    {
        $owners = collect([]);

        for ($i = 0; $i < $count; $i++) {
            $owners->add(new class() extends Model
            {
                protected $table = 'owners';

                public $timestamps = false;

                use HasWallets;
            });
        }

        return $owners->count() > 1 ? $owners : $owners->first();
    }
}
