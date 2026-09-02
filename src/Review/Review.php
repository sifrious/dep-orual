<?php

declare(strict_types=1);

namespace Sifrious\Orual\Review;

use DateTimeImmutable;

/**
 * A persisted review: its identity, its subject, its state, and its content.
 */
final readonly class Review implements Reviewable
{
    public function __construct(
        public int $id,
        public CheckinReference $subject,
        public ReviewContent $content,
        public ?DateTimeImmutable $dateCompleted,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
    ) {}

    public function state(): ReviewState
    {
        return ReviewState::fromCompletionTimestamp($this->dateCompleted);
    }

    public function subject(): CheckinReference
    {
        return $this->subject;
    }

    /**
     * @return array<string, mixed>
     */
    public function getContent(): array
    {
        return $this->content->toArray();
    }

    public function getProductivityScore(): ?int
    {
        return $this->content->productivityScore();
    }

    public function getQualityScore(): ?int
    {
        return $this->content->qualityScore();
    }

    public function isComplete(): bool
    {
        return $this->state()->isComplete();
    }

    public function headline(): string
    {
        return $this->content->headline();
    }
}
