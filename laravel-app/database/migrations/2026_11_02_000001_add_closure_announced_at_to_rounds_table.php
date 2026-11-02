<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rounds', function (Blueprint $table) {
            // When the circle was mailed that the session is over, so it is
            // only ever announced once. Null while it has not been.
            $table->timestamp('closure_announced_at')->nullable()->after('end_at');
        });

        // The sessions already over are not announced after the fact.
        DB::table('rounds')
            ->whereNotNull('end_at')
            ->where('end_at', '<=', now())
            ->update(['closure_announced_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rounds', function (Blueprint $table) {
            $table->dropColumn('closure_announced_at');
        });
    }
};
