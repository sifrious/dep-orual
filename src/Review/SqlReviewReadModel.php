<?php

declare(strict_types=1);

namespace Sifrious\Orual\Review;

use Illuminate\Database\ConnectionInterface;

final class SqlReviewReadModel implements ReviewReadModel
{
    public function __construct(private readonly ConnectionInterface $connection) {}

    public function find(int $id): ?Review
    {
        $row = $this->connection->table(ReviewHydrator::TABLE)->where('id', $id)->first();

        return $row === null ? null : ReviewHydrator::fromRow($row);
    }

    public function forSubject(CheckinReference $subject): array
    {
        $rows = $this->connection->table(ReviewHydrator::TABLE)
            ->where('checkin_id', $subject->id)
            ->orderBy('id')
            ->get();

        return $this->hydrate($rows->all());
    }

    public function inState(ReviewState $state): array
    {
        $query = $this->connection->table(ReviewHydrator::TABLE);

        $query = $state->isComplete()
            ? $query->whereNotNull('date_completed')
            : $query->whereNull('date_completed');

        return $this->hydrate($query->orderBy('id')->get()->all());
    }

    public function recent(int $limit = 50): array
    {
        $rows = $this->connection->table(ReviewHydrator::TABLE)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        return $this->hydrate($rows->all());
    }

    /**
     * @param  array<int, object>  $rows
     * @return list<Review>
     */
    private function hydrate(array $rows): array
    {
        return array_values(array_map(
            static fn (object $row): Review => ReviewHydrator::fromRow($row),
            $rows,
        ));
    }
}
