<?php

namespace SaasPro\Subscriptions\Models;

use Carbon\Carbon;
use SaasPro\Subscriptions\Enums\PaymentGateways;
use SaasPro\Subscriptions\Enums\Subscriptions\SubscriptionActions;
use SaasPro\Subscriptions\Enums\SubscriptionStatus;
use SaasPro\Subscriptions\Events\Subscriptions\SubscriptionStatusUpdated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SaasPro\Subscriptions\Models\Plans\PlanPrice;
use SaasPro\Subscriptions\Models\Plans\Plan;
use SaasPro\Subscriptions\Models\Transactions\Transaction;
use SaasPro\Subscriptions\Services\TransactionService;
use SaasPro\Support\State;

class Subscription extends Model {
    
    protected $fillable = ['user_id', 'plan_id', 'plan_price_id', 'expires_at', 'starts_at', 'auto_renews', 'meta', 'status', 'grace_ends_at', 'cancelled_at'];

    protected $casts = [
        'status' => SubscriptionStatus::class,
        'meta' => 'array',
        'expires_at' => 'datetime',
        'starts_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'grace_ends_at' => 'datetime',
        'cancelled_at' => 'datetime'
    ];  

    public function subscriber(){
        return $this->morphTo();
    }

    public function planPrice() {
        return $this->belongsTo(PlanPrice::class, 'plan_price_id');
    }

    public function price() {
        return $this->belongsTo(PlanPrice::class, 'plan_price_id');
    }

    public function plan(){
        return $this->belongsTo(Plan::class, 'plan_id');
    }

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

    // Methods
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

    


}
