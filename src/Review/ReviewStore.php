<?php

declare(strict_types=1);

namespace Sifrious\Orual\Review;

use DateTimeImmutable;

/**
 * Commands over review state. Landing submits these; it does not write the
 * package tables itself.
 */
interface ReviewStore
{
    public function record(ReviewDraft $draft): Review;

    /**
     * Move a review to complete, stamping `date_completed`.
     *
     * @throws ReviewNotFound
     */
    public function complete(int $id, ?DateTimeImmutable $at = null): Review;

    /**
     * Replace a review's content, leaving its state and subject untouched.
     *
     * @throws ReviewNotFound
     */
    public function reviseContent(int $id, ReviewContent $content): Review;
}
