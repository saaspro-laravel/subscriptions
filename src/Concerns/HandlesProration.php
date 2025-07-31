<?php

namespace SaasPro\Subscriptions\Concerns;

use SaasPro\Subscriptions\Models\PlanPrice;
use SaasPro\Subscriptions\Models\Subscription;

trait HandlesProration {

    function calculateProration(Subscription $subscription, PlanPrice $planPrice) {
        $unusedDays = $subscription->days - $subscription->days_used;
        $dailyRate = $subscription->transaction->amount / $subscription->days;

        return round($unusedDays * $dailyRate, 2);
    }

}