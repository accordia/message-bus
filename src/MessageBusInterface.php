<?php
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\MessageBus;

use Daikon\MessageBus\Metadata\MetadataInterface;

interface MessageBusInterface
{
    public function publish(MessageInterface $message, string $channel, MetadataInterface $metadata = null): void;

    public function receive(EnvelopeInterface $envelope): void;
}
