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
        Schema::table('round_user', function (Blueprint $table) {
            // When the plume played a joker on this session: she hands nothing
            // in and still reads the others once it closes. Null otherwise.
            $table->timestamp('joker_at')->nullable()->after('round_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('round_user', function (Blueprint $table) {
            $table->dropColumn('joker_at');
        });
    }
};
