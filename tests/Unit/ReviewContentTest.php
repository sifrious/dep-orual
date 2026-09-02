<?php

declare(strict_types=1);

use Sifrious\Orual\Review\InvalidReviewContent;
use Sifrious\Orual\Review\ReviewContent;

it('exposes the content keys Landing guarantees', function (): void {
    $content = ReviewContent::fromArray([
        'summary' => 'Shipped the extraction slice.',
        'observations' => ['tests green', 'no schema drift'],
        'productivity_score' => 8,
        'quality_score' => 7,
    ]);

    expect($content->summary())->toBe('Shipped the extraction slice.')
        ->and($content->observations())->toBe(['tests green', 'no schema drift'])
        ->and($content->productivityScore())->toBe(8)
        ->and($content->qualityScore())->toBe(7)
        ->and($content->headline())->toBe('Shipped the extraction slice.');
});

it('falls back to Landing\'s pending headline when there is no summary', function (): void {
    expect(ReviewContent::empty()->headline())->toBe('Review pending');
});

it('treats both scores as optional', function (): void {
    $content = ReviewContent::fromArray(['summary' => 'No ratings recorded.']);

    expect($content->productivityScore())->toBeNull()
        ->and($content->qualityScore())->toBeNull();
});

it('preserves keys written by other workflows', function (): void {
    $content = ReviewContent::fromArray(['summary' => 'x', 'interrupt_kind' => 'avoidance']);

    expect($content->toArray())->toBe(['summary' => 'x', 'interrupt_kind' => 'avoidance']);
});

it('rejects scores outside the documented 1-10 range', function (int $score): void {
    ReviewContent::fromArray(['productivity_score' => $score]);
})->with([0, 11, -3])->throws(InvalidReviewContent::class);

it('rejects a non-integer score', function (): void {
    ReviewContent::fromArray(['quality_score' => '9']);
})->throws(InvalidReviewContent::class);

it('rejects a non-string summary', function (): void {
    ReviewContent::fromArray(['summary' => ['nope']]);
})->throws(InvalidReviewContent::class);

it('rejects observations that are not a list', function (): void {
    ReviewContent::fromArray(['observations' => ['first' => 'nope']]);
})->throws(InvalidReviewContent::class);
