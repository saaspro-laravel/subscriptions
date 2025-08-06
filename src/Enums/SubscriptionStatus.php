<?php

namespace SaasPro\Subscriptions\Enums;

enum SubscriptionStatus: string {

    case ACTIVE = 'active';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';
    case TRIALING = 'trialing';
    case GRACE = 'grace';  

    public function label(): string {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::CANCELLED => 'Cancelled',
            self::EXPIRED => 'Expired',
            self::TRIALING => 'Trialing',
            self::GRACE => 'Grace Period',
        };
    }

    function color(){
        return match ($this) {
            self::ACTIVE => 'success',
            self::CANCELLED => 'warning',
            self::EXPIRED => 'danger',
            self::TRIALING => 'info',
            self::GRACE => 'secondary',
        };
    }

}