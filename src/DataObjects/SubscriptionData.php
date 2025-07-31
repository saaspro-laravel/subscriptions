<?php

namespace SaasPro\Subscriptions\DataObjects;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SaasPro\Enums\Timelines;
use SaasPro\Subscriptions\Models\Plan;
use SaasPro\Subscriptions\Models\Subscription;

/**
 * Class SubscriptionData
 *
 * @property Model|null $subscriber
 * @property Plan|null $plan
 * @property Subscription|null $subscription
 * @property Timelines|null $timeline
 * @property bool|null $auto_renews
 * @property Carbon|string $expires_at
 * @property Carbon|string $starts_at
 * @property Carbon|string $grace_ends_at
 * @property Carbon|string $cancelled_at
 * @property string|null $provider
 * @property string|null $provider_id
 * @property array $meta
 * @property string $status
 *
 * @phpstan-type SubscriptionDataArray array{
 *     subscriber?: Model|null,
 *     plan?: Plan|null,
 *     subscription?: Subscription|null,
 *     timeline?: Timelines|null,
 *     auto_renews?: bool|null,
 *     expires_at?: Carbon|string,
 *     starts_at?: Carbon|string,
 *     grace_ends_at?: Carbon|string,
 *     cancelled_at?: Carbon|string,
 *     provider?: string|null,
 *     provider_id?: string|null,
 *     meta?: array,
 *     status?: string,
 * }
 */
final class SubscriptionData {

    public ?Model $subscriber = null;
    public ?Plan $plan = null;
    public ?Subscription $subscription = null;
    public ?Timelines $timeline = null;
    public ?bool $auto_renews = null;
    public Carbon|string $expires_at;
    public Carbon|string $starts_at;
    public Carbon|string $grace_ends_at;
    public Carbon|string $cancelled_at;
    public ?string $provider = null;
    public ?string $provider_id = null;
    public array $meta = [];
    public string $status = '';

    /**
     * @param array<string, mixed> $data
     * @return static
     *
     * @phpstan-param SubscriptionDataArray $data
     */
    public static function make(array $data = []): self {
        return (new self())->fill($data);
    }

    /**
     * @param array<string, mixed> $items
     * @return $this
     *
     * @phpstan-param SubscriptionDataArray $items
     */
    public function fill(array $items): self {
        foreach ($items as $key => $item) {
            if (property_exists($this, $key)) {
                $this->{$key} = $item;
            }
        }
        return $this;
    }

    /**
     * @param Subscription $subscription
     * @return static
     */
    public static function from(Subscription $subscription): self {
        $instance = self::make($subscription->toArray());
        $instance->setSubscription($subscription);
        return $instance;
    }

    /**
     * @param Subscription $subscription
     * @param bool $fill
     * @return $this
     */
    public function setSubscription(Subscription $subscription, bool $fill = false): self {
        $this->subscription = $subscription;
        if ($fill) $this->fill($subscription->toArray());
        return $this;
    }

    /**
     * @param string $provider
     * @param string|null $provider_id
     * @return $this
     */
    public function withProvider(string $provider, ?string $provider_id = null): self {
        $this->provider = $provider;
        if ($provider_id) {
            $this->provider_id = $provider_id;
        }
        return $this;
    }

    /**
     * @param string $provider_id
     * @return $this
     */
    public function withProviderId(string $provider_id): self {
        $this->provider_id = $provider_id;
        return $this;
    }

    /**
     * @param Model $subscriber
     * @return $this
     */
    public function setSubscriber(Model $subscriber): self {
        $this->subscriber = $subscriber;
        return $this;
    }

    /**
     * @param Plan $plan
     * @return $this
     */
    public function setPlan(Plan $plan): self {
        $this->plan = $plan;
        return $this;
    }

    /**
     * @param Timelines $timeline
     * @return $this
     */
    public function setTimeline(Timelines $timeline): self {
        $this->timeline = $timeline;
        return $this;
    }

    /**
     * @param Carbon|string $grace_period
     * @return $this
     */
    public function graceEndsAt(Carbon|string $grace_period): self {
        $this->grace_ends_at = $grace_period;
        return $this;
    }

    /**
     * @param Carbon|string $date
     * @return $this
     */
    public function cancelAt(Carbon|string $date): self {
        $this->cancelled_at = $date;
        return $this;
    }

    /**
     * @param Carbon|string $date
     * @return $this
     */
    public function startAt(Carbon|string $date): self {
        $this->starts_at = $date;
        return $this;
    }

    /**
     * @param Carbon|string $endAt
     * @return $this
     */
    public function endAt(Carbon|string $endAt): self {
        $this->expires_at = $endAt;
        return $this;
    }

    /**
     * Convert object to collection
     *
     * @return Collection<string, mixed>
     *
     * @phpstan-return Collection<string, mixed>
     */
    public function collect(): Collection {
        return collect(get_object_vars($this));
    }

    /**
     * Convert to typed array
     *
     * @return array<string, mixed>
     *
     * @phpstan-return SubscriptionDataArray
     */
    public function toArray(): array {
        return $this->collect()->toArray();
    }
}
