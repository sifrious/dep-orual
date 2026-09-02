<?php

declare(strict_types=1);

use Sifrious\Orual\Review\CheckinReference;
use Sifrious\Orual\Review\ReviewContent;
use Sifrious\Orual\Review\ReviewDraft;
use Sifrious\Orual\Review\ReviewReadModel;
use Sifrious\Orual\Review\ReviewState;
use Sifrious\Orual\Review\ReviewStore;

beforeEach(function (): void {
    $this->store = app(ReviewStore::class);
    $this->reviews = app(ReviewReadModel::class);

    $this->pending = $this->store->record(ReviewDraft::pending(
        CheckinReference::of(1),
        ReviewContent::fromArray(['summary' => 'Pending one.']),
    ));

    $this->complete = $this->store->record(ReviewDraft::complete(
        CheckinReference::of(1),
        ReviewContent::fromArray(['summary' => 'Complete one.']),
        new DateTimeImmutable('2026-08-29 06:00:00'),
    ));

    $this->other = $this->store->record(ReviewDraft::pending(
        CheckinReference::of(2),
        ReviewContent::empty(),
    ));
});

it('finds a review by id', function (): void {
    expect($this->reviews->find($this->complete->id)?->headline())->toBe('Complete one.')
        ->and($this->reviews->find(404))->toBeNull();
});

it('lists every review for one subject', function (): void {
    $found = $this->reviews->forSubject(CheckinReference::of(1));

    expect(array_map(fn ($review): int => $review->id, $found))
        ->toBe([$this->pending->id, $this->complete->id]);
});

it('filters by review state', function (): void {
    expect(array_map(fn ($r): int => $r->id, $this->reviews->inState(ReviewState::Pending)))
        ->toBe([$this->pending->id, $this->other->id])
        ->and(array_map(fn ($r): int => $r->id, $this->reviews->inState(ReviewState::Complete)))
        ->toBe([$this->complete->id]);
});

it('lists recent reviews newest first and honours the limit', function (): void {
    expect(array_map(fn ($r): int => $r->id, $this->reviews->recent()))
        ->toBe([$this->other->id, $this->complete->id, $this->pending->id])
        ->and($this->reviews->recent(1))->toHaveCount(1);
});

it('exposes status and result detail for adapters that only read', function (): void {
    $review = $this->reviews->find($this->complete->id);

    expect($review?->state()->value)->toBe('complete')
        ->and($review?->isComplete())->toBeTrue()
        ->and($review?->getContent())->toBe(['summary' => 'Complete one.'])
        ->and($review?->headline())->toBe('Complete one.');
});
