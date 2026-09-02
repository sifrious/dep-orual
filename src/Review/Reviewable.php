<?php

declare(strict_types=1);

namespace Sifrious\Orual\Review;

/**
 * The read side of Landing's `App\Contracts\Reviewable`, restated without
 * Eloquent so adapters can substitute their own implementation.
 *
 * Landing's `checkin(): BelongsTo` becomes `subject()`, and its
 * `markComplete()` moves to {@see ReviewStore}: completing a review is a
 * command against the store, not a mutation of a read value.
 */
interface Reviewable
{
    public function subject(): CheckinReference;

    /**
     * @return array<string, mixed>
     */
    public function getContent(): array;

    public function getProductivityScore(): ?int;

    public function getQualityScore(): ?int;

    public function isComplete(): bool;

    public function headline(): string;
}
