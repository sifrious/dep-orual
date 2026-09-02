# Orual

Editorial review state and decisions over referenced artifacts.

Orual owns *whether something has been reviewed* and *what the review said*. It
does not own the thing under review: source evidence stays in Funes, generated
drafts stay in Pulp, and browser pages stay in the consuming application.

## Scope of this slice

This package currently covers the review shape Landing already persists — the
`reviews` table behind `App\Models\Review` and `App\Contracts\Reviewable`:

| Landing column   | Orual                                                   |
| ---------------- | ------------------------------------------------------- |
| `id`             | `Review::$id`                                           |
| `checkin_id`     | `CheckinReference` — a reference, never a copied checkin |
| `date_completed` | `ReviewState::Pending` / `ReviewState::Complete`        |
| `content`        | `ReviewContent` (`summary`, `observations`, `productivity_score`, `quality_score`, plus any other recorded key) |
| `timestamps`     | `Review::$createdAt` / `Review::$updatedAt`             |

Approve, reject, request-revision, reviewer identity, quorum, and supersession
are **not** modelled. Landing records none of them, so representing them here
would make migrated rows assert decisions nobody made. They belong to a later
ticket with source-supported behaviour.

## Contracts

Consumers depend on two interfaces and never on the package tables.

```php
use Sifrious\Orual\Review\{CheckinReference, ReviewContent, ReviewDraft, ReviewReadModel, ReviewState, ReviewStore};

// Commands
$review = app(ReviewStore::class)->record(ReviewDraft::pending(
    CheckinReference::of($checkinId),
    ReviewContent::fromArray([
        'summary' => 'Sweep half done.',
        'observations' => ['two files left'],
    ]),
));

app(ReviewStore::class)->complete($review->id);
app(ReviewStore::class)->reviseContent($review->id, $content);

// Queries
$reviews = app(ReviewReadModel::class);
$reviews->find($id);
$reviews->forSubject(CheckinReference::of($checkinId));
$reviews->inState(ReviewState::Pending);
$reviews->recent(20);
```

`Review` implements `Reviewable`, the Eloquent-free restatement of Landing's
read-side contract: `subject()`, `getContent()`, `getProductivityScore()`,
`getQualityScore()`, `isComplete()`, `headline()`. Landing's `markComplete()`
moves to `ReviewStore::complete()` — completing is a command against the store,
not a mutation of a read value.

### Completion is not terminal

Landing's `Review::markComplete()` writes `date_completed => now()` with no
guard, so re-completing an already complete review restamps it. Orual
reproduces that, under test, rather than inventing a terminal rule the source
does not have.

## Installation

```
composer require sifrious/orual
```

Migrations load automatically. To publish the config:

```
php artisan vendor:publish --tag=orual-config
```

`orual.connection` selects the database connection (`null` uses the default).

The package table is `orual_reviews`. It mirrors Landing's `reviews` columns
exactly, minus the `checkin_id` foreign-key constraint — the checkin is owned
by another package, so Orual holds a stable reference instead of a
database-level dependency.

## Development

```
composer test   # pest
composer lint   # pint
```
