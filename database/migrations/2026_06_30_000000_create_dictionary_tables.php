<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dictionary_words', function (Blueprint $table) {
            $table->id();
            $table->string('headword');
            $table->string('normalized_headword')->unique();
            $table->text('meaning');
            $table->string('source')->nullable();
            $table->timestamps();
        });

        Schema::create('dictionary_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dictionary_word_id')->constrained()->cascadeOnDelete();
            $table->string('alias');
            $table->string('normalized_alias')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dictionary_aliases');
        Schema::dropIfExists('dictionary_words');
    }
};
