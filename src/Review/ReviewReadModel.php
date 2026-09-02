<?php

declare(strict_types=1);

namespace Sifrious\Orual\Review;

/**
 * Queries over review state, for adapters that inspect reviews without
 * reading Orual's tables.
 */
interface ReviewReadModel
{
    public function find(int $id): ?Review;

    /**
     * @return list<Review>
     */
    public function forSubject(CheckinReference $subject): array;

    /**
     * @return list<Review>
     */
    public function inState(ReviewState $state): array;

    /**
     * Most recent first.
     *
     * @return list<Review>
     */
    public function recent(int $limit = 50): array;
}
