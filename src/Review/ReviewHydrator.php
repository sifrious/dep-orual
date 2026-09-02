<?php

declare(strict_types=1);

namespace Sifrious\Orual\Review;

use DateTimeImmutable;
use stdClass;

/**
 * @internal
 */
final class ReviewHydrator
{
    public const TABLE = 'orual_reviews';

    public const TIMESTAMP_FORMAT = 'Y-m-d H:i:s';

    public static function fromRow(stdClass $row): Review
    {
        /** @var array<string, mixed> $content */
        $content = json_decode((string) $row->content, true, 512, JSON_THROW_ON_ERROR) ?: [];

        return new Review(
            id: (int) $row->id,
            subject: CheckinReference::of((int) $row->checkin_id),
            content: ReviewContent::fromArray($content),
            dateCompleted: self::toDate($row->date_completed),
            createdAt: self::toDate($row->created_at) ?? new DateTimeImmutable,
            updatedAt: self::toDate($row->updated_at) ?? new DateTimeImmutable,
        );
    }

    public static function format(DateTimeImmutable $moment): string
    {
        return $moment->format(self::TIMESTAMP_FORMAT);
    }

    private static function toDate(mixed $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        return new DateTimeImmutable((string) $value);
    }
}
