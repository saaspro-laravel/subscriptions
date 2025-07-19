<?php

namespace Utyemma\SaasPro\Concerns\Subscriptions;

use SaasPro\Subscriptions\Models\Subscription;

trait CanSubscribe {

    public function subscriptions(){
        return $this->morphMany(Subscription::class, 'subscriber');
    }

    public function subscription(){
        return $this->morphOne(Subscription::class, 'subscriber')->isActive();
    }

    function isSubscribed(){

    }

    function plan(){
        return $this->subscription->plan();
    }

}