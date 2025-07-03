<?php

namespace SaasPro\Subscriptions;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Reflection;
use ReflectionClass;
use SaasPro\Enums\Timelines;
use SaasPro\Subscriptions\Models\Plans\Plan;
use SaasPro\Subscriptions\Models\Subscription;

class SubscriptionBuilder {

    private Carbon | null $trial_ends_at = null;
    private Carbon $ends_at;
    private Carbon $starts_at;
    private Carbon $grace_ends_at;
    private Carbon $expires_at;
    private array $meta;
    private bool $auto_renews;

    private int $duration;

    function __construct(private $subscriber, private Plan $plan) {
        $this->starts_at = now();
    }

    function withSubscriber($subscriber){
        $this->subscriber = $subscriber;
        return $this;
    }

    function toPlan(Plan $plan) {
        $this->plan = $plan;
        return $this;
    }

    function trialEndsAt(string | Carbon | null $date = null) {
        if($date) $this->trial_ends_at = Carbon::parse($date);
        return $this->trial_ends_at;
    }

    function willStartAt(string | Carbon $start_date){
        $this->starts_at = Carbon::parse($start_date);
        return $this;
    }

    function willExpireAt(string | Carbon $end_date) {
        $this->expires_at = Carbon::parse($end_date);
        return $this;
    }

    function expiresAt(string | Carbon | null $expiry_date = null){
        if($expiry_date) $this->expires_at = Carbon::parse($expiry_date);
        return $this->expires_at;
    }

    public function withTrialPeriod(int $duration, ?Timelines $timeline = null){
        $timeline ??= Timelines::DAY;
        $this->trial_ends_at = $this->ends_at->add($timeline->value, $duration);
        return $this;
    }

    public function meta(){
        return $this->meta;
    }

    public function withMeta(array $meta) {
        $this->meta = $meta;
        return $this;
    }

    public function startsAt(string | Carbon | null $start_date = null){
        if($start_date) return $start_date;
        if($this->starts_at) return $this->starts_at;
        if($trial_ends_at = $this->trialEndsAt()) return $trial_ends_at;
        return null;
    }

    public function graceEndsAt(){
        return $this->ends_at;
    }

    public function hasGracePeriod(int $duration, ?Timelines $timeline = null){
        $timeline ??= Timelines::DAY;
        $this->grace_ends_at = $this->ends_at?->add($timeline->value, $duration);
        return $this;
    }

    public function autoRenews(bool | null $auto_renew = null){
        if(is_null($auto_renew)) return $this->auto_renews;
        return $auto_renew;
    }

    public function shouldAutoRenew(bool $auto_renew = true){
        $this->auto_renews = $auto_renew;
        return $this;
    }

    function new(){
        return new Subscription($this->toArray());
    }

    function build(){

    }

    function toArray(){
        return [
            'trial_ends_at' => $this->trialEndsAt(),
            'starts_at' => $this->startsAt(),
            'grace_ends_at' => $this->graceEndsAt(),
            'expires_at' => $this->expiresAt(),
            'meta' => $this->meta(),
            'auto_renews' => $this->autoRenews()
        ];
    }

}