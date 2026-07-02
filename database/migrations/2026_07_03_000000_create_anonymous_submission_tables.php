<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('entries', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('definitions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('definitions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('anonymous_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('term');
            $table->string('normalized_term')->index();
            $table->text('meaning');
            $table->text('example')->nullable();
            $table->string('status')->default('pending')->index();
            $table->foreignId('published_entry_id')->nullable()->constrained('entries')->nullOnDelete();
            $table->foreignId('published_definition_id')->nullable()->constrained('definitions')->nullOnDelete();
            $table->string('submitter_ip_hash', 64)->nullable()->index();
            $table->string('submitter_user_agent_hash', 64)->nullable();
            $table->timestamps();
        });

        Schema::create('anonymous_submission_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anonymous_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('vote');
            $table->timestamps();
            $table->unique(['anonymous_submission_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anonymous_submission_votes');
        Schema::dropIfExists('anonymous_submissions');

        DB::table('definitions')->whereNull('user_id')->delete();
        DB::table('entries')->whereNull('user_id')->delete();

        Schema::table('definitions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('definitions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('entries', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('entries', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
