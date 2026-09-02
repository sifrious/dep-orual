<?php

declare(strict_types=1);

namespace Sifrious\Orual\Review;

use DateTimeImmutable;
use Illuminate\Database\ConnectionInterface;

final class SqlReviewStore implements ReviewStore
{
    public function __construct(private readonly ConnectionInterface $connection) {}

    public function record(ReviewDraft $draft): Review
    {
        $now = ReviewHydrator::format(new DateTimeImmutable);

        $id = (int) $this->connection->table(ReviewHydrator::TABLE)->insertGetId([
            'checkin_id' => $draft->subject->id,
            'date_completed' => $draft->dateCompleted === null
                ? null
                : ReviewHydrator::format($draft->dateCompleted),
            'content' => json_encode($draft->content->toArray(), JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->require($id);
    }

    public function complete(int $id, ?DateTimeImmutable $at = null): Review
    {
        $this->require($id);

        $this->connection->table(ReviewHydrator::TABLE)
            ->where('id', $id)
            ->update([
                'date_completed' => ReviewHydrator::format($at ?? new DateTimeImmutable),
                'updated_at' => ReviewHydrator::format(new DateTimeImmutable),
            ]);

        return $this->require($id);
    }

    public function reviseContent(int $id, ReviewContent $content): Review
    {
        $this->require($id);

        $this->connection->table(ReviewHydrator::TABLE)
            ->where('id', $id)
            ->update([
                'content' => json_encode($content->toArray(), JSON_THROW_ON_ERROR),
                'updated_at' => ReviewHydrator::format(new DateTimeImmutable),
            ]);

        return $this->require($id);
    }

    private function require(int $id): Review
    {
        $row = $this->connection->table(ReviewHydrator::TABLE)->where('id', $id)->first();

        if ($row === null) {
            throw ReviewNotFound::withId($id);
        }

        return ReviewHydrator::fromRow($row);
    }
}
