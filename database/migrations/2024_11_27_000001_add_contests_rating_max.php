<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Constants
     */
    private const TABLE_NAME  = 'contests';
    private const COLUMN_NAME = 'rating_max';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn(self::TABLE_NAME, self::COLUMN_NAME)) {
            Schema::table(self::TABLE_NAME, function (Blueprint $table) {
                $table->unsignedTinyInteger(self::COLUMN_NAME)->default(5);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn(self::TABLE_NAME, self::COLUMN_NAME)) {
            Schema::table(self::TABLE_NAME, function (Blueprint $table) {
                $table->dropColumn(self::COLUMN_NAME);
            });
        }
    }
};
