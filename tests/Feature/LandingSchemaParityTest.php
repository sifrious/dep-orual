<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Sifrious\Orual\Review\CheckinReference;
use Sifrious\Orual\Review\ReviewContent;
use Sifrious\Orual\Review\ReviewDraft;
use Sifrious\Orual\Review\ReviewStore;

/**
 * Regression evidence for the attached Landing stories: the extraction must
 * not drop a column the original `reviews` table persisted.
 *
 * Source: landing/database/migrations/2026_04_24_170001_create_reviews_table.php
 */
it('preserves every column of the Landing reviews table', function (): void {
    $columns = Schema::getColumnListing('orual_reviews');

    sort($columns);

    expect($columns)->toBe([
        'checkin_id',
        'content',
        'created_at',
        'date_completed',
        'id',
        'updated_at',
    ]);
});

it('persists the content keys the Landing Reviewable contract names', function (): void {
    $review = app(ReviewStore::class)->record(ReviewDraft::complete(
        CheckinReference::of(4),
        ReviewContent::fromArray([
            'summary' => 'Full parity payload.',
            'observations' => ['one', 'two'],
            'productivity_score' => 10,
            'quality_score' => 1,
        ]),
        new DateTimeImmutable('2026-08-29 06:00:00'),
    ));

    $stored = json_decode((string) DB::table('orual_reviews')->where('id', $review->id)->value('content'), true);

    expect($stored)->toBe([
        'summary' => 'Full parity payload.',
        'observations' => ['one', 'two'],
        'productivity_score' => 10,
        'quality_score' => 1,
    ]);
});
