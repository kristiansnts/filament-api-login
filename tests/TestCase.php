<?php

namespace Kristiansnts\FilamentApiLogin\Tests;

use Kristiansnts\FilamentApiLogin\FilamentApiLoginServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            FilamentApiLoginServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        config()->set('filament-api-login.api_url', 'http://test-api.com/auth');
        config()->set('filament-api-login.timeout', 30);
        config()->set('filament-api-login.log_failures', false);
    }
}