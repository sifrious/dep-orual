<?php

declare(strict_types=1);

namespace Sifrious\Orual\Review;

use DateTimeImmutable;

/**
 * The review states Landing's schema actually supports.
 *
 * State is derived from `date_completed` alone. Approval, rejection, and
 * requested revision are not represented: the source records carry no column
 * or code path that distinguishes them, and inventing them here would make
 * migrated rows claim decisions nobody recorded.
 */
enum ReviewState: string
{
    case Pending = 'pending';
    case Complete = 'complete';

    public static function fromCompletionTimestamp(?DateTimeImmutable $dateCompleted): self
    {
        return $dateCompleted === null ? self::Pending : self::Complete;
    }

    public function isComplete(): bool
    {
        return $this === self::Complete;
    }
}
