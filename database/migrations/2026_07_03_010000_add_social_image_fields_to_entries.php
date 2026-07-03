<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->string('og_image_path')->nullable()->after('is_hidden');
            $table->timestamp('og_image_generated_at')->nullable()->after('og_image_path');
            $table->text('og_image_error')->nullable()->after('og_image_generated_at');
        });
    }

    public function down(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->dropColumn(['og_image_path', 'og_image_generated_at', 'og_image_error']);
        });
    }
};
