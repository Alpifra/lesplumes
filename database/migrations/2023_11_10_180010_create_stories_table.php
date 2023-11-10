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
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')
                  ->constrained(table: 'rounds')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->foreignId('writer_id')
                  ->constrained(table: 'users')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            // set media_id relation in keeping with package medias table
            $table->integer('media_id')
                  ->nullable()
                  ->unsigned();
            $table->foreign('media_id')
                  ->references('id')
                  ->on('medias')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
