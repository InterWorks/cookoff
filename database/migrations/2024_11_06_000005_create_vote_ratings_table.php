<?php

use App\Models\Entry;
use App\Models\RatingFactor;
use App\Models\Vote;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vote_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Vote::class);
            $table->foreignIdFor(Entry::class);
            $table->foreignIdFor(RatingFactor::class);
            $table->decimal('rating', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vote_ratings');
    }
};
