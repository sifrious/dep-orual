<?php

declare(strict_types=1);

namespace Sifrious\Orual\Review;

use InvalidArgumentException;

final class InvalidReviewContent extends InvalidArgumentException
{
    public static function scoreOutOfRange(string $key, int $score): self
    {
        return new self("Review content key '{$key}' must be between 1 and 10, got {$score}.");
    }

    public static function scoreNotAnInteger(string $key): self
    {
        return new self("Review content key '{$key}' must be an integer or absent.");
    }

    public static function summaryNotAString(): self
    {
        return new self("Review content key 'summary' must be a string.");
    }

    public static function observationsNotAList(): self
    {
        return new self("Review content key 'observations' must be a list.");
    }
}
