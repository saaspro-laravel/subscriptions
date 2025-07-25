<?php

namespace Utyemma\SaasPro\Concerns\Subscriptions;

use SaasPro\Enums\Timelines;
use SaasPro\Subscriptions\DataObjects\SubscriptionData;
use SaasPro\Subscriptions\Models\Plans\Plan;
use SaasPro\Subscriptions\Models\Subscription;
use SaasPro\Subscriptions\SubscriptionBuilder;

trait CanSubscribe {

    protected static function bootHasSubscriptions(): void
    {
        static::deleted(function ($subscriber): void {
            $subscriber->subscriptions()->delete();
        });
    }

    public function subscriptions(){
        return $this->morphMany(Subscription::class, 'subscriber');
    }
    
    function activeSubscriptions() {
        return $this->morphMany(Subscription::class, 'subscriber')->isActive();
    }

    public function getSubscriptionAttribute($name = 'default'){
        return $this->activeSubscriptions()->whereName($name)->first();
    }

    function isSubscribed(){
        return (bool) $this->subscription;
    }

    function plan(){
        return $this->subscription?->plan;
    }

    function subscribe(SubscriptionData $subscriptionData){
        return Subscription::create($subscriptionData->toArray()); 
    }

}