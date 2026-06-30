<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moderation_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('reportable');
            $table->string('reason');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'reportable_type', 'reportable_id']);
        });

        Schema::create('moderation_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_type')->default('community');
            $table->string('event_type');
            $table->morphs('subject');
            $table->string('reason')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();
        });

        Schema::create('appeals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('appealable');
            $table->text('statement');
            $table->string('status')->default('open')->index();
            $table->unsignedInteger('restore_votes_count')->default(0);
            $table->unsignedInteger('keep_hidden_votes_count')->default(0);
            $table->timestamps();
        });

        Schema::create('appeal_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appeal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('vote');
            $table->timestamps();
            $table->unique(['appeal_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appeal_votes');
        Schema::dropIfExists('appeals');
        Schema::dropIfExists('moderation_events');
        Schema::dropIfExists('moderation_reports');
    }
};
