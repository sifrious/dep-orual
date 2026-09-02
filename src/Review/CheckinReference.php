<?php

declare(strict_types=1);

namespace Sifrious\Orual\Review;

use InvalidArgumentException;

/**
 * Stable reference to the subject of a review.
 *
 * Landing's `reviews.checkin_id` is the only subject binding the source
 * records. Orual stores the reference and never copies the checkin, its
 * evidence, or any generated content.
 */
final readonly class CheckinReference
{
    private function __construct(public int $id) {}

    public static function of(int $id): self
    {
        if ($id < 1) {
            throw new InvalidArgumentException('A checkin reference must be a positive identifier.');
        }

        return new self($id);
    }

    public function equals(self $other): bool
    {
        return $this->id === $other->id;
    }

    public function __toString(): string
    {
        return 'checkin:'.$this->id;
    }
}
