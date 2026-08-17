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
        Schema::create('words', function (Blueprint $table) {
            $table->id();
            // The dictionary is reference data, replaced wholesale by
            // `words:import`: a word carries no history of its own.
            //
            // Indexed but not unique: MySQL collates accent-insensitively,
            // so a unique index would take "âcre" for a duplicate of "acre"
            // and swallow four hundred words. The file is deduplicated at
            // the source, and the import empties the table beforehand.
            $table->string('word', 50)->index();
            $table->string('category', 16);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('words');
    }
};
