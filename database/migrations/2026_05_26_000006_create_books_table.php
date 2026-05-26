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
        Schema::create('books', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->foreignId('publisher_id')->nullable()->constrained()->nullOnDelete();
            $table->date('published_at');
            $table->unsignedMediumInteger('word_count'); // the biggest book has ~2m words.
            $table->decimal('price', 10, 2);
            $table->char('currency', 3); // ISO 4217.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
