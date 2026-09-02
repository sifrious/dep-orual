<?php

declare(strict_types=1);

use Sifrious\Orual\Review\CheckinReference;
use Sifrious\Orual\Review\ReviewContent;
use Sifrious\Orual\Review\ReviewDraft;
use Sifrious\Orual\Review\ReviewNotFound;
use Sifrious\Orual\Review\ReviewState;
use Sifrious\Orual\Review\ReviewStore;

beforeEach(function (): void {
    $this->store = app(ReviewStore::class);
});

it('records a pending review against its subject', function (): void {
    $review = $this->store->record(ReviewDraft::pending(
        CheckinReference::of(42),
        ReviewContent::fromArray([
            'summary' => 'Halfway through the sweep.',
            'observations' => ['two files left'],
        ]),
    ));

    expect($review->state())->toBe(ReviewState::Pending)
        ->and($review->isComplete())->toBeFalse()
        ->and($review->dateCompleted)->toBeNull()
        ->and($review->subject()->id)->toBe(42)
        ->and($review->headline())->toBe('Halfway through the sweep.');
});

it('records a review that is complete on arrival', function (): void {
    $completedAt = new DateTimeImmutable('2026-08-29 06:15:00');

    $review = $this->store->record(ReviewDraft::complete(
        CheckinReference::of(9),
        ReviewContent::fromArray([
            'summary' => 'Checkin closed.',
            'observations' => [],
            'productivity_score' => 6,
            'quality_score' => 9,
        ]),
        $completedAt,
    ));

    expect($review->state())->toBe(ReviewState::Complete)
        ->and($review->isComplete())->toBeTrue()
        ->and($review->dateCompleted?->format('Y-m-d H:i:s'))->toBe('2026-08-29 06:15:00')
        ->and($review->getProductivityScore())->toBe(6)
        ->and($review->getQualityScore())->toBe(9);
});

it('moves a pending review to complete', function (): void {
    $review = $this->store->record(ReviewDraft::pending(
        CheckinReference::of(3),
        ReviewContent::fromArray(['summary' => 'Started.']),
    ));

    $completed = $this->store->complete($review->id, new DateTimeImmutable('2026-08-30 12:00:00'));

    expect($completed->id)->toBe($review->id)
        ->and($completed->state())->toBe(ReviewState::Complete)
        ->and($completed->dateCompleted?->format('Y-m-d H:i:s'))->toBe('2026-08-30 12:00:00')
        ->and($completed->subject()->id)->toBe(3)
        ->and($completed->getContent())->toBe(['summary' => 'Started.']);
});

/**
 * Characterization, not a design choice: Landing's Review::markComplete()
 * writes `date_completed => now()` with no guard, so completion is not
 * terminal in the source. Orual reproduces that until a ticket with
 * source-supported supersession behavior says otherwise.
 */
it('restamps completion because the source treats it as non-terminal', function (): void {
    $review = $this->store->record(ReviewDraft::complete(
        CheckinReference::of(5),
        ReviewContent::empty(),
        new DateTimeImmutable('2026-08-29 06:00:00'),
    ));

    $again = $this->store->complete($review->id, new DateTimeImmutable('2026-08-31 08:00:00'));

    expect($again->dateCompleted?->format('Y-m-d H:i:s'))->toBe('2026-08-31 08:00:00');
});

it('refuses to complete a review that does not exist', function (): void {
    $this->store->complete(404);
})->throws(ReviewNotFound::class);

it('refuses to revise content for a review that does not exist', function (): void {
    $this->store->reviseContent(404, ReviewContent::empty());
})->throws(ReviewNotFound::class);

it('revises content without disturbing subject or state', function (): void {
    $review = $this->store->record(ReviewDraft::complete(
        CheckinReference::of(11),
        ReviewContent::fromArray(['summary' => 'First pass.']),
        new DateTimeImmutable('2026-08-29 06:00:00'),
    ));

    $revised = $this->store->reviseContent(
        $review->id,
        ReviewContent::fromArray(['summary' => 'Second pass.', 'observations' => ['tightened']]),
    );

    expect($revised->headline())->toBe('Second pass.')
        ->and($revised->subject()->id)->toBe(11)
        ->and($revised->state())->toBe(ReviewState::Complete)
        ->and($revised->dateCompleted?->format('Y-m-d H:i:s'))->toBe('2026-08-29 06:00:00');
});

it('stores the subject by reference and never copies the checkin', function (): void {
    $this->store->record(ReviewDraft::pending(CheckinReference::of(77), ReviewContent::empty()));

    $row = DB::table('orual_reviews')->first();

    expect((int) $row->checkin_id)->toBe(77)
        ->and(json_decode((string) $row->content, true))->toBe([]);
});
