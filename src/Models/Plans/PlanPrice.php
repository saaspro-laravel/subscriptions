<?php

namespace SaasPro\Subscriptions\Models\Plans;

use SaasPro\Subscriptions\Concerns\Models\HasStatus;
use Illuminate\Database\Eloquent\Model;
use SaasPro\Subscriptions\Models\Plans\PlanCountryPrice;
use Illuminate\Database\Eloquent\Casts\Attribute;
use SaasPro\Enums\Timelines;

class PlanPrice extends Model {
    use HasStatus;
    
    protected $fillable = ['plan_id', 'amount', 'provider_id', 'timeline'];


    protected $casts = [
        'timeline' => Timelines::class
    ];

    protected $with = ['prices'];

    protected $append = ['price'];

    protected function amount(): Attribute {
        return Attribute::make(
            get: function($value) {
                if($this->prices->count() && $price = $this->prices()->isCurrent()->first()) return $price->price;
                return $value;
            }
        );
    }

    protected function providerId(){
        return Attribute::make(
            get: function($value) {
                if($this->prices->count() && $price = $this->prices()->isCurrent()->first()) return $price->provider_id;
                return $value;
            }
        );
    }

    function plan(){
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    function prices(){
        return $this->hasMany(PlanCountryPrice::class, 'price_id');
    }

    // function getPriceAttribute(){
    //     if($this->prices->count() && $price = $this->prices()->isCurrent()->first()) return $price->price;
    //     return $this->amount;
    // }


}
