<?php

declare(strict_types=1);

namespace Sifrious\Orual\Review;

/**
 * The structured body Landing stores in `reviews.content`.
 *
 * `summary` and `observations` are the keys Landing's Reviewable contract
 * guarantees; `productivity_score` and `quality_score` are the optional 1-10
 * ratings it documents. Any other key written by an existing workflow is
 * preserved verbatim so migration never drops recorded content.
 */
final readonly class ReviewContent
{
    private const SCORE_KEYS = ['productivity_score', 'quality_score'];

    /**
     * @param  array<string, mixed>  $values
     */
    private function __construct(private array $values) {}

    /**
     * @param  array<string, mixed>  $values
     */
    public static function fromArray(array $values): self
    {
        if (array_key_exists('summary', $values) && ! is_string($values['summary'])) {
            throw InvalidReviewContent::summaryNotAString();
        }

        if (array_key_exists('observations', $values) && $values['observations'] !== null) {
            if (! is_array($values['observations']) || ! array_is_list($values['observations'])) {
                throw InvalidReviewContent::observationsNotAList();
            }
        }

        foreach (self::SCORE_KEYS as $key) {
            if (! array_key_exists($key, $values) || $values[$key] === null) {
                continue;
            }

            if (! is_int($values[$key])) {
                throw InvalidReviewContent::scoreNotAnInteger($key);
            }

            if ($values[$key] < 1 || $values[$key] > 10) {
                throw InvalidReviewContent::scoreOutOfRange($key, $values[$key]);
            }
        }

        return new self($values);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->values;
    }

    public function summary(): ?string
    {
        $summary = $this->values['summary'] ?? null;

        return is_string($summary) ? $summary : null;
    }

    /**
     * @return list<mixed>
     */
    public function observations(): array
    {
        $observations = $this->values['observations'] ?? [];

        return is_array($observations) && array_is_list($observations) ? $observations : [];
    }

    public function productivityScore(): ?int
    {
        return $this->score('productivity_score');
    }

    public function qualityScore(): ?int
    {
        return $this->score('quality_score');
    }

    /**
     * Landing's one-line dashboard summary, including its pending fallback.
     */
    public function headline(): string
    {
        return $this->summary() ?? 'Review pending';
    }

    private function score(string $key): ?int
    {
        $score = $this->values[$key] ?? null;

        return is_int($score) ? $score : null;
    }
}
