<?php

namespace SaasPro\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use SaasPro\Features\Models\FeatureItem;

class Plan extends Model {
    use SoftDeletes;
    
    protected $fillable = ['name', 'description', 'is_popular', 'status', 'trial_period', 'sort', 'grace_period', 'is_default', 'is_free'];

    protected $casts = [
        'is_popular' => 'boolean',
        'is_default' => 'boolean',
        'status' => 'boolean',
        'is_free' => 'boolean',
    ];

    public function scopeIsPaid($query) {
        $query->where('is_free', false);
    }

    public function prices(){
        return $this->hasMany(PlanPrice::class);
    }

    public function features(){
        return $this->morphMany(FeatureItem::class, 'featureable');
    }

    public function hasTrial(){
        return (bool) $this->trial_period;
    }

    public function hasGrace(){
        return (bool) $this->grace_period;
    }

    public function isFree(): bool {
        return (bool) $this->is_free;
    }


}
