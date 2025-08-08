<?php

namespace SaasPro\Subscriptions\Models;

use Carbon\Carbon;
use Exception;
use SaasPro\Concerns\Models\HasHistory;
use SaasPro\Contracts\SavesToHistory;
use SaasPro\Subscriptions\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use SaasPro\Enums\Timelines;
use SaasPro\Subscriptions\Events\SubscriptionCancelled;
use SaasPro\Subscriptions\Events\SubscriptionCreated;
use SaasPro\Subscriptions\Events\SubscriptionEnded;
use SaasPro\Subscriptions\Events\SubscriptionRenewed;
use SaasPro\Subscriptions\Events\SubscriptionResumed;
use SaasPro\Subscriptions\Events\SubscriptionUpdated;
use SaasPro\Support\Token;

class Subscription extends Model implements SavesToHistory {
    use HasHistory;
    
    // 'provider', 'provider_id',
    protected $fillable = ['user_id', 'name', 'plan_id', 'price_id', 'expires_at', 'starts_at', 'meta', 'grace_ends_at', 'cancelled_at', 'reference'];

    protected $casts = [
        'timeline' => Timelines::class,
        'meta' => 'array',
        'expires_at' => 'datetime',
        'starts_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'grace_ends_at' => 'datetime',
        'cancelled_at' => 'datetime'
    ];  

    protected $attributes = [
        'name' => 'default'
    ];

    static public function booted(){
        self::creating(function($subscription){
            if(!$subscription->ends_at) {
                $price = $subscription->price;
                $subscription->expires_at = $subscription->starts_at->add($price->timeline->days(), 'days');
            }

            if(!$subscription->reference) {
                $subscription->reference = Token::random(8)->prepend('SUB-')->upper()->unique(Subscription::class, 'reference');
            }
        });

        self::created(function($subscription){
            if($subscription->isActive()) {
                SubscriptionCreated::dispatch($subscription);
            }
        });
    }

    function getHistoryEvent($event){
        return $this->status;
    }

    function getHistoryEntityName(): string{
        return "Subscription";
    }

    function getHistoryEditorName(): string{
        return $this->subscriber_title;
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
    public function getSubscriberTitleAttribute(){
        return $this->subscriber->getSubscriberTitle();
    }

    public function getDaysUsedAttribute(){
        return $this->starts_at->diffInDays(now());
    }

    public function getDaysAttribute(){
        return $this->starts_at->diffInDays($this->expires_at);
    }

    public function active (): bool {
        return !$this->cancelled() && !$this->ended();
    }

    function onTrial(): bool{
        return $this->trial_ends_at && now()->lt($this->trial_ends_at);
    }

    function onGrace(): bool{
        return $this->expired() && $this->grace_ends_at && now()->lt($this->grace_ends_at);
    }

    function ended(): bool{
        return $this->expired() && !$this->onGrace();
    }

    public function expired(): bool {
        return $this->expires_at && now()->gte($this->expires_at);
    }

    public function cancelled(): bool {
        return !!$this->cancelled_at;
    }

    function canResume(): bool{
        return $this->cancelled() && !$this->ended();
    }

    public function getStatusAttribute(): SubscriptionStatus {
        return match (true) {
            $this->active() => SubscriptionStatus::ACTIVE,
            $this->expired() => SubscriptionStatus::EXPIRED,
            $this->cancelled() => SubscriptionStatus::CANCELLED,
            $this->onTrial() => SubscriptionStatus::TRIALING,
            $this->onGrace() => SubscriptionStatus::GRACE,
            default => SubscriptionStatus::ACTIVE,
        };
    }

    public function getAutoRenewsAttribute(){
        return $this->cancelled() == false;
    }

    // Actions
    function renew(Carbon $ends_at, ?bool $force = false){
        if($this->ended() && !$force) {
            throw new \Exception('Your subscription is already expired. Please create a new subscription to continue.');
        }
        
        $this->ends_at = $ends_at;
        $this->cancelled_at = null;
        $this->save();

        SubscriptionRenewed::dispatch($this);

        return $this;
    }

    function cancel(bool $immediately = false) {
        $this->cancelled_at = now();

        if($immediately) {
            $this->expires_at = $this->cancelled_at;
        }

        $this->save();

        $immediately ? SubscriptionEnded::dispatch($this) : SubscriptionCancelled::dispatch($this);

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

        SubscriptionUpdated::dispatch($this);

        return $this;
    }

    function resume(){
        if(!$this->canResume()) {
            throw new Exception('Unable to resume subscription.');
        }

        $this->cancelled_at = null;
        $this->expires_at = $this->starts_at->add($this->price->timeline->days(), 'days');
        $this->save();

        SubscriptionResumed::dispatch($this);

        return $this;
    }

    function usage(){

    }


}
