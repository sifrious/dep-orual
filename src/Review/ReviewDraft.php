<?php

declare(strict_types=1);

namespace Sifrious\Orual\Review;

use DateTimeImmutable;

/**
 * The command payload for recording a review.
 */
final readonly class ReviewDraft
{
    public function __construct(
        public CheckinReference $subject,
        public ReviewContent $content,
        public ?DateTimeImmutable $dateCompleted = null,
    ) {}

    public static function pending(CheckinReference $subject, ReviewContent $content): self
    {
        return new self($subject, $content);
    }

    public static function complete(
        CheckinReference $subject,
        ReviewContent $content,
        DateTimeImmutable $dateCompleted,
    ): self {
        return new self($subject, $content, $dateCompleted);
    }

    public function state(): ReviewState
    {
        return ReviewState::fromCompletionTimestamp($this->dateCompleted);
    }
}
