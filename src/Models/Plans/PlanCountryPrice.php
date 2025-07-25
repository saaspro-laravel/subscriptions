<?php

namespace Utyemma\SaasPro\Models\Plans;

use Utyemma\SaasPro\Models\Country;
use Illuminate\Database\Eloquent\Model;

class PlanCountryPrice extends Model {
    
    protected $fillable = ['country_id', 'price_id', 'provider_id', 'price'];

    function scopeIsCurrent($query){
        if($country = locale()->country()) $query->whereCountryId($country?->id);
    }
    
    function country(){
        return $this->belongsTo(Country::class, 'country_id');
    }

}
