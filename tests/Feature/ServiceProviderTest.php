<?php

declare(strict_types=1);

use Sifrious\Orual\Review\ReviewReadModel;
use Sifrious\Orual\Review\ReviewStore;
use Sifrious\Orual\Review\SqlReviewReadModel;
use Sifrious\Orual\Review\SqlReviewStore;

it('binds the public review contracts', function (): void {
    expect(app(ReviewStore::class))->toBeInstanceOf(SqlReviewStore::class)
        ->and(app(ReviewReadModel::class))->toBeInstanceOf(SqlReviewReadModel::class);
});

it('publishes a connection setting', function (): void {
    expect(config('orual.connection'))->toBeNull();
});
