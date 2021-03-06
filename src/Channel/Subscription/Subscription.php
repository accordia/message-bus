<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\MessageBus\Channel\Subscription;

use Closure;
use Daikon\MessageBus\Channel\Subscription\MessageHandler\MessageHandlerList;
use Daikon\MessageBus\Channel\Subscription\Transport\TransportInterface;
use Daikon\MessageBus\EnvelopeInterface;
use Daikon\MessageBus\Exception\EnvelopeNotAcceptable;
use Daikon\MessageBus\MessageBusInterface;
use Daikon\Metadata\MetadataInterface;
use Daikon\Metadata\MetadataEnricherInterface;
use Daikon\Metadata\MetadataEnricherList;

final class Subscription implements SubscriptionInterface
{
    private string $key;

    private TransportInterface $transport;

    private MessageHandlerList $messageHandlers;

    private Closure $guard;

    private MetadataEnricherList $metadataEnrichers;

    public function __construct(
        string $key,
        TransportInterface $transport,
        MessageHandlerList $messageHandlers,
        Closure $guard = null,
        MetadataEnricherList $metadataEnrichers = null
    ) {
        $this->key = $key;
        $this->transport = $transport;
        $this->messageHandlers = $messageHandlers;
        $this->guard = $guard ?? fn(): bool => true;
        $metadataEnrichers = $metadataEnrichers ?? new MetadataEnricherList;
        $this->metadataEnrichers = $metadataEnrichers->enrichWith(self::METADATA_KEY, $this->key);
    }

    public function publish(EnvelopeInterface $envelope, MessageBusInterface $messageBus): void
    {
        $envelope = $this->enrichMetadata($envelope);
        if ($this->accepts($envelope)) {
            $this->transport->send($envelope, $messageBus);
        }
    }

    public function receive(EnvelopeInterface $envelope): void
    {
        $this->verify($envelope);
        foreach ($this->messageHandlers as $messageHandler) {
            $messageHandler->handle($envelope);
        }
    }

    public function getKey(): string
    {
        return $this->key;
    }

    private function enrichMetadata(EnvelopeInterface $envelope): EnvelopeInterface
    {
        return $envelope->withMetadata(array_reduce(
            $this->metadataEnrichers->unwrap(),
            function (MetadataInterface $metadata, MetadataEnricherInterface $metadataEnricher): MetadataInterface {
                return $metadataEnricher->enrich($metadata);
            },
            $envelope->getMetadata()
        ));
    }

    private function accepts(EnvelopeInterface $envelope): bool
    {
        return (bool)($this->guard)($envelope);
    }

    private function verify(EnvelopeInterface $envelope): void
    {
        $metadata = $envelope->getMetadata();
        if (!$metadata->has(self::METADATA_KEY)) {
            throw new EnvelopeNotAcceptable(
                "Subscription key '".self::METADATA_KEY."' missing in metadata of Envelope ".
                "'{$envelope->getUuid()->toString()}' received by subscription '{$this->key}'.",
                EnvelopeNotAcceptable::SUBSCRIPTION_KEY_MISSING
            );
        }
        $subscriptionKey = $metadata->get(self::METADATA_KEY);
        if ($subscriptionKey !== $this->key) {
            throw new EnvelopeNotAcceptable(
                "Subscription '{$this->key}' inadvertently received Envelope ".
                "'{$envelope->getUuid()->toString()}' for subscription '$subscriptionKey'.",
                EnvelopeNotAcceptable::SUBSCRIPTION_KEY_UNEXPECTED
            );
        }
    }
}
