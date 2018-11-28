<?php
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\MessageBus;

use Daikon\Interop\FromNativeInterface;
use Daikon\Interop\ToNativeInterface;
use Daikon\MessageBus\Metadata\MetadataInterface;
use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

interface EnvelopeInterface extends FromNativeInterface, ToNativeInterface
{
    const TIMESTAMP_FORMAT = "Y-m-d\\TH:i:s.uP";

    public static function wrap(MessageInterface $message, MetadataInterface $metadata = null): EnvelopeInterface;

    public function getTimestamp(): DateTimeImmutable;

    public function getUuid(): UuidInterface;

    public function getMetadata(): MetadataInterface;

    public function withMetadata(MetadataInterface $metadata): EnvelopeInterface;

    public function getMessage(): MessageInterface;
}
