<?php
namespace GravityLegal\GravityLegalAPI;

use Illuminate\Support\ServiceProvider;

class GravityLegalServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }
    public function register()
    {
        $this->app->bind('gravity-legal', function() {
            return new GravityLegalService();
        });
    }
}