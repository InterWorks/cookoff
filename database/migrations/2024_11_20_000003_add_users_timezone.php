<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Constants
     */
    private const TABLE_NAME  = 'users';
    private const COLUMN_NAME = 'timezone';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn(self::TABLE_NAME, self::COLUMN_NAME)) {
            Schema::table(self::TABLE_NAME, function (Blueprint $table): void {
                $table->string(self::COLUMN_NAME)->nullable()->after('email_verified_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn(self::TABLE_NAME, self::COLUMN_NAME)) {
            Schema::table(self::TABLE_NAME, function (Blueprint $table): void {
                $table->dropColumn(self::COLUMN_NAME);
            });
        }
    }
};
