<?php

namespace SaasPro\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use SaasPro\Locale\Models\Country;

class PlanCountryPrice extends Model {
    
    protected $fillable = ['country_id', 'price_id', 'price'];

    function scopeIsCurrent($query){
        if($country = locale()->country()) $query->whereCountryId($country?->id);
    }
    
    function country(){
        return $this->belongsTo(Country::class, 'country_id');
    }

}
