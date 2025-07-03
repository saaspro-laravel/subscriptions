<?php

namespace Utyemma\SaasPro\Concerns\Subscriptions;

use Utyemma\SaasPro\Models\Plans\PlanPrice;
use Utyemma\SaasPro\Models\Subscription;

trait HandlesProration {

    function calculateProration(Subscription $subscription, PlanPrice $planPrice) {
        $unusedDays = $subscription->days - $subscription->days_used;
        $dailyRate = $subscription->transaction->amount / $subscription->days;

        return round($unusedDays * $dailyRate, 2);
    }

}