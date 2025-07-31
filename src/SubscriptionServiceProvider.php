<?php

namespace SaasPro\Subscriptions;

use Illuminate\Support\ServiceProvider;

class SubscriptionServiceProvider extends ServiceProvider {

    function register(){
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    function boot(){
        
    }

}