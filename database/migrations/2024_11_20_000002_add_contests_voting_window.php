<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Constants
     */
    private const TABLE_NAME                     = 'contests';
    private const COLUMN_VOTING_WINDOW_OPENS_AT  = 'voting_window_opens_at';
    private const COLUMN_VOTING_WINDOW_CLOSES_AT = 'voting_window_closes_at';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn(self::TABLE_NAME, self::COLUMN_VOTING_WINDOW_OPENS_AT)) {
            Schema::table(self::TABLE_NAME, function (Blueprint $table) {
                $table->timestamp(self::COLUMN_VOTING_WINDOW_OPENS_AT)
                    ->nullable()
                    ->after('updated_at');
                $table->timestamp(self::COLUMN_VOTING_WINDOW_CLOSES_AT)
                    ->nullable()
                    ->after(self::COLUMN_VOTING_WINDOW_OPENS_AT);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn(self::TABLE_NAME, self::COLUMN_VOTING_WINDOW_OPENS_AT)) {
            Schema::table(self::TABLE_NAME, function (Blueprint $table) {
                $table->dropColumn(self::COLUMN_VOTING_WINDOW_OPENS_AT);
            });
        }

        if (Schema::hasColumn(self::TABLE_NAME, self::COLUMN_VOTING_WINDOW_CLOSES_AT)) {
            Schema::table(self::TABLE_NAME, function (Blueprint $table) {
                $table->dropColumn(self::COLUMN_VOTING_WINDOW_CLOSES_AT);
            });
        }
    }
};
