<?php

namespace SaasPro\Subscriptions;

use SaasPro\Enums\Timelines;
use SaasPro\Subscriptions\Models\Plan;

class Subscriptions {

    public static function pricing(){
        return collect(Timelines::cases())->mapWithKeys(function($timeline){
            $plans = Plan::whereRelation('prices', 'timeline', $timeline)->with(['prices'])->orderBy('sort')->get();

            return [
                $timeline->value => [
                    'name' => $timeline->label(),
                    'plans' => $plans,
                ]
            ];
        });
    }

}