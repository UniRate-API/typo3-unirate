<?php

declare(strict_types=1);

namespace UniRate\Typo3\Service;

/**
 * Raised when a UniRate API request fails (auth, rate limit, bad currency, transport, or decode).
 *
 * The HTTP status is carried as the exception code (0 for transport/decode errors).
 */
final class UniRateException extends \RuntimeException
{
}
