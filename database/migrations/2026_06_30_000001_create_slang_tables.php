<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('term');
            $table->string('slug')->unique();
            $table->string('normalized_term')->index();
            $table->boolean('is_hidden')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('meaning');
            $table->text('example')->nullable();
            $table->unsignedInteger('votes_count')->default(0)->index();
            $table->boolean('is_hidden')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('definition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['definition_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
        Schema::dropIfExists('definitions');
        Schema::dropIfExists('entries');
    }
};
