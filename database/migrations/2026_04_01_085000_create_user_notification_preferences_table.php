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
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('document_changes_email')->default(true);
            $table->boolean('document_changes_browser')->default(true);
            $table->boolean('comments_email')->default(true);
            $table->boolean('comments_browser')->default(true);
            $table->boolean('mentions_email')->default(true);
            $table->boolean('mentions_browser')->default(true);
            $table->boolean('shares_email')->default(true);
            $table->boolean('shares_browser')->default(true);
            $table->boolean('reviews_email')->default(true);
            $table->boolean('reviews_browser')->default(true);
            $table->boolean('push_enabled')->default(false);
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notification_preferences');
    }
};
