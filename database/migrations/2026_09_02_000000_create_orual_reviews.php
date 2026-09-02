<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mirrors Landing's `reviews` table (2026_04_24_170001_create_reviews_table).
 *
 * Every column of the source table is preserved. The `checkin_id` foreign key
 * constraint is deliberately dropped: the checkin is owned by another package,
 * so Orual holds a stable reference rather than a database-level dependency.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orual_reviews', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('checkin_id');
            $table->timestamp('date_completed')->nullable();
            $table->json('content');
            $table->timestamps();

            $table->index('checkin_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orual_reviews');
    }
};
