<?php

declare(strict_types=1);

use Sifrious\Orual\Review\CheckinReference;
use Sifrious\Orual\Review\ReviewState;

it('derives state from the completion timestamp alone', function (): void {
    expect(ReviewState::fromCompletionTimestamp(null))->toBe(ReviewState::Pending)
        ->and(ReviewState::fromCompletionTimestamp(new DateTimeImmutable('2026-08-29 10:00:00')))
        ->toBe(ReviewState::Complete);
});

it('supports only the two states Landing records', function (): void {
    expect(array_map(fn (ReviewState $state): string => $state->value, ReviewState::cases()))
        ->toBe(['pending', 'complete']);
});

it('requires a positive subject identifier', function (): void {
    CheckinReference::of(0);
})->throws(InvalidArgumentException::class);

it('compares subject references by identity', function (): void {
    expect(CheckinReference::of(7)->equals(CheckinReference::of(7)))->toBeTrue()
        ->and(CheckinReference::of(7)->equals(CheckinReference::of(8)))->toBeFalse()
        ->and((string) CheckinReference::of(7))->toBe('checkin:7');
});
