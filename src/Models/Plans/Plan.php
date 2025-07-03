<?php

namespace SaasPro\Subscriptions\Models\Plans;

use SaasPro\Subscriptions\Concerns\Models\HasStatus;
use SaasPro\Subscriptions\Models\Features\Feature;
use SaasPro\Subscriptions\Models\Features\PlanFeature;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model {
    use SoftDeletes, HasStatus;
    
    protected $fillable = ['name', 'description', 'is_popular', 'trial_period', 'sort', 'grace_period', 'is_default', 'is_free'];

    protected $casts = [
        'is_popular' => 'boolean',
        'is_default' => 'boolean',
        'is_free' => 'boolean',
    ];

    function scopeIsPaid($query) {
        $query->where('is_free', false);
    }

    function features(){
        return $this->belongsToMany(Feature::class, 'plan_features')
            ->withPivot(['id', 'limit', 'reset_period', 'reset_interval'])
            ->as('feature')
            ->withTimestamps();
    }

    function prices(){
        return $this->hasMany(PlanPrice::class);
    }

    function hasTrial(){
        return (bool) $this->trial_period;
    }

    function hasGrace(){
        return (bool) $this->grace_period;
    }

    function isFree(): bool {
        return (bool) $this->is_free;
    }


}
