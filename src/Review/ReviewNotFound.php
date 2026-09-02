<?php

declare(strict_types=1);

namespace Sifrious\Orual\Review;

use RuntimeException;

final class ReviewNotFound extends RuntimeException
{
    public static function withId(int $id): self
    {
        return new self("No review exists with id {$id}.");
    }
}
