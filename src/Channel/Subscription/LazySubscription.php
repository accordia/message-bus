<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\MessageBus\Channel\Subscription;

use Closure;
use Daikon\MessageBus\EnvelopeInterface;
use Daikon\MessageBus\MessageBusInterface;

final class LazySubscription implements SubscriptionInterface
{
    private string $key;

    private ?SubscriptionInterface $compositeSubscription;

    private ?Closure $factoryCallback;

    public function __construct(
        string $key,
        Closure $transport,
        Closure $messageHandlers,
        Closure $guard = null,
        Closure $metadataEnrichers = null
    ) {
        $this->key = $key;
        $this->factoryCallback = fn(): SubscriptionInterface =>
            new Subscription(
                $this->key,
                $transport(),
                $messageHandlers(),
                $guard,
                $metadataEnrichers ? $metadataEnrichers() : null
            );
    }

    public function publish(EnvelopeInterface $envelope, MessageBusInterface $messageBus): void
    {
        $this->getSubscription()->publish($envelope, $messageBus);
    }

    public function receive(EnvelopeInterface $envelope): void
    {
        $this->getSubscription()->receive($envelope);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    private function getSubscription(): SubscriptionInterface
    {
        if ($this->factoryCallback) {
            $this->compositeSubscription = call_user_func($this->factoryCallback);
            $this->factoryCallback = null;
        }
        return $this->compositeSubscription;
    }
}
