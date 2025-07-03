<?php

namespace Utyemma\SaasPro\Services;

use Utyemma\SaasPro\Contracts\Payment\HandlesSubscriptionRenewal;
use Utyemma\SaasPro\Enums\PaymentStatus;
use Utyemma\SaasPro\Enums\Subscriptions\SubscriptionActions;
use Utyemma\SaasPro\Enums\SubscriptionStatus;
use Utyemma\SaasPro\Models\Plans\PlanPrice;
use Utyemma\SaasPro\Models\Plans\Timeline;
use Utyemma\SaasPro\Models\Subscription;
use Utyemma\SaasPro\Models\Transactions\Transaction;
use Utyemma\SaasPro\Support\Locale;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class Subscription {

    public function __construct(
        private TransactionService $transactionService
    ) { }

    static function make(){
        $locale = new Locale;
        $transactionService = new TransactionService();
        return new static($transactionService);
    }

    function start($user, $planPrice, array $data = []) {
        try {
            DB::beginTransaction();
            $planPrice->load(['plan', 'timeline']);
            
            $plan = $planPrice->plan;
            if($plan->trial_period) $this->withTrial($plan->trial_period); 
            $subscription = $this->create($user, $planPrice, $data);
            
            if($plan->trial_period) return state(true, '', $subscription);

            [$status, $message, $data] = $this->initiate($subscription);
            if($status) DB::commit();

            return state($status, $message, $data);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    function initiate(Subscription $subscription){
        $transaction = $this->transactionService->create($subscription, $subscription->planPrice->amount);
        $paymentProvider = $transaction->provider();
        return $paymentProvider->subscribe($transaction);        
    }

    function subscribe(Transaction $transaction) {
        $subscription = $transaction->transactable;

        [$status, $message, $transaction] = $this->transactionService->verify($transaction);

        if(!$status) return state(false, $message);

        if($transaction->status == PaymentStatus::SUCCESS) {
            $subscription->activate();
        }

        return state(true, '', $subscription);
    }

    public $trial = null;

    function withTrial($interval){
        $this->trial = $interval;
        return $this;
    }

    function create($user, PlanPrice $planPrice, array $data = []) {
        $plan = $planPrice->plan;
        $trial_ends_at = $this->trial ? now()->addDays((int) $this->trial) : null;
        $starts_at = $trial_ends ?? now();

        $ends_at = Date::parse($starts_at)->add($planPrice->timeline->timeline->value, (int) $planPrice->timeline->count);
        $grace_ends_at = $plan->grace_period ? Date::parse($ends_at)->addDays((int) $plan->grace_period) : null;

        return $user->subscriptions()->create([
            'plan_id' => $planPrice->plan_id,
            'plan_price_id' => $planPrice->id,
            'expires_at' => $ends_at,
            'starts_at' => $starts_at,
            'auto_renews' => true,
            'trial_ends_at' => $trial_ends_at,
            'grace_ends_at' => $grace_ends_at,
            'status' => $this->trial ? SubscriptionStatus::TRIAL : SubscriptionStatus::PENDING
        ]);
    }
    
    function sendExpirationWarning($days = 7) {
        $expiringSubscriptions = Subscription::isExpiring($days)->with('user')->get('user');
        $users = $expiringSubscriptions->pluck('user')->unique('id');

        notify("Your subscription will expire in {$days} days")
            ->line("Just a reminder — your current subscription will expire in {{$days}} days. To avoid any interruptions in service, please make sure to renew your subscription before it ends.")
            ->action('Manage Subscription', '')
            ->priority(1)
            ->sendNow($users, ['mail']);
    }

    function cancel(Subscription $subscription){
        $response = $subscription->provider->cancelSubscription($subscription);

        if(!$response->success()) return state(false, $response->message()); 

        return state(true, $response->message());
    }

    function upgrade(Subscription $subscription, PlanPrice $planPrice) {
        if($subscription->planPrice->is($planPrice)) {
            return state(false, "You are already on the {$planPrice->plan->name} plan! You may upgrade to a different plan");
        }

        if(!$plan = $planPrice->plan) {
            return state(false, "The selected plan does not exist");
        }

        $subscription->provider->upgrade($subscription, $planPrice);
    }

    

    function expiredSubscriptions($date = null) {
        return Subscription::hasExpired($date)->get();
    }

    public function handleExpiredSubscriptions(Subscription $subscription) {
        if($subscription->status == SubscriptionStatus::EXPIRED) {
            return state(true, 'Subscription is already expired');
        }

        if($subscription->auto_renews) {
            if($subscription->provider instanceof HandlesSubscriptionRenewal) {
                [$status, $message, $data] = $subscription->provider->renew($subscription);
    
                if($status) {
                    $subscription->saveHistory(SubscriptionActions::RENEWED, $data);
                    return state(true, 'Subscription renewed successfully');
                }
    
                $subscription->saveHistory(SubscriptionActions::RENEWAL_FAILED, $data);
            } 
        }

        if($subscription->grace_ends_at && $subscription->grace_ends_at->isFuture()) {
            $subscription->grace();
            return state(true, 'Subscription is in grace period');
        }

        $subscription->expired();

        return state(true, 'Subscription marked as expired');
    }
    
    function markSubscriptionAsExpired(Subscription $subscription, $gracePeriod = false) {
        
    }

}
