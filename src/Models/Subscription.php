<?php

namespace SaasPro\Subscriptions\Models;

use Carbon\Carbon;
use Exception;
use SaasPro\Subscriptions\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SaasPro\Enums\Timelines;

class Subscription extends Model {
    
    protected $fillable = ['user_id', 'name', 'plan_id', 'price_id', 'timeline', 'expires_at', 'starts_at', 'auto_renews', 'meta', 'status', 'grace_ends_at', 'cancelled_at', 'provider', 'provider_id', 'reference'];

    protected $casts = [
        'status' => SubscriptionStatus::class,
        'timeline' => Timelines::class,
        'meta' => 'array',
        'expires_at' => 'datetime',
        'starts_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'grace_ends_at' => 'datetime',
        'cancelled_at' => 'datetime'
    ];  

    protected $attribute = [
        'name' => 'default'
    ];

    static public function booted(){
        self::creating(function($subscription){
            if(!$subscription->ends_at) {
                $subscription->ends_at = Carbon::parse($subscription->starts_at)->add($subscription->timeline->days());
            }
        });
    }

    // Relationships
    public function subscriber(){
        return $this->morphTo();
    }

    public function plan(){
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    function price() {
        return $this->belongsTo(PlanPrice::class, 'price_id');
    }

    // Scopes
    public function scopeIsActive(Builder $query){
        $query->whereBeforeToday('expires_at')->orWhereAfterToday('trial_ends_at');
    }
    
    function scopeIsGrace(Builder $query){
        $query->isExpired()->whereNowOrFuture('grace_ends_at');
    }

    public function scopeIsExpired(Builder $builder) {
        $builder->wherePast('expires_at');
    }

    public function scopeIsExpiring(Builder $builder, $days = 7) {
        $builder->isActive()->where('expires_at', '<=', now()->addDays($days));
    }

    // Attributes
    public function getDaysUsedAttribute(){
        return $this->starts_at->diffInDays(now());
    }

    public function getDaysAttribute(){
        return $this->starts_at->diffInDays($this->expires_at);
    }

    public function active () {
        return !$this->cancelled() || !$this->expired();
    }

    function onTrial(){
        return $this->trial_ends_at && now()->lt($this->trial_ends_at);
    }

    function onGrace(){
        return $this->expired() && $this->grace_ends_at && now()->lt($this->grace_ends_at);
    }

    function ended(){
        return $this->expired() && !$this->onGrace();
    }

    public function expired(): bool {
        return $this->expires_at && now()->gte($this->expires_at);
    }

    public function cancelled(){
        return $this->cancelled_at;
    }

    function renew(?Carbon $ends_at = null, ?bool $force = false){
        if($this->ended() || !$force) {
            throw new \Exception('Unable to renew canceled ended subscription.');
        }
        
        $this->ends_at = $ends_at ?? now()->add($this->interval);
        $this->cancelled_at = null;
        $this->save();

        return $this;
    }

    function cancel(bool $immediately = false) {
        $this->cancelled_at = now();

        if($immediately) {
            $this->expires_at = $this->cancelled_at;
        }

        $this->save();
        return $this;
    }

    function swap(Plan $plan, Timelines | null $timeline = null) {
        if($timeline) {
            $this->timeline = $timeline;
        }
        
        if(!$price = $plan->prices()->whereTimeline($this->timeline)->first()){
            throw new Exception("The {$this->timeline->label()} timeline does not exist on plan {$plan->name}");
        }

        $this->price_id = $price->id;
        $this->plan_id = $plan;
        $this->save();

        return $this;
    }

    function usage(){

    }


}
