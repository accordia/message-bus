<?php
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\MessageBus\Channel\Subscription;

use Daikon\DataStructure\TypedMapTrait;
use Daikon\MessageBus\Error\ConfigurationError;

final class SubscriptionMap implements \IteratorAggregate, \Countable
{
    use TypedMapTrait;

    public function __construct(array $subscriptions = [])
    {
        $this->init(array_reduce($subscriptions, function (array $carry, SubscriptionInterface $subscription): array {
            $subscriptionKey = $subscription->getKey();
            if (isset($carry[$subscriptionKey])) {
                throw new ConfigurationError("Subscription key '$subscriptionKey' is already defined.");
            }
            $carry[$subscriptionKey] = $subscription; // enforce consistent channel keys
            return $carry;
        }, []), SubscriptionInterface::class);
    }
}
