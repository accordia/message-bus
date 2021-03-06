<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\MessageBus\Exception;

use Daikon\Interop\RuntimeException;

final class ChannelUnknown extends RuntimeException implements MessageBusException
{
}
