<?php

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
        Schema::table('rounds', function (Blueprint $table) {
            // The plume this round's master handed the word over to. Null
            // while nobody handed it over: the turn then follows the circle.
            $table->foreignId('next_master_id')
                  ->nullable()
                  ->after('master_id')
                  ->constrained(table: 'users')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rounds', function (Blueprint $table) {
            $table->dropConstrainedForeignId('next_master_id');
        });
    }
};
