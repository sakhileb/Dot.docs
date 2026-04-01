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
        Schema::table('document_versions', function (Blueprint $table) {
            $table->boolean('is_milestone')->default(false)->after('is_auto_save');
            $table->string('milestone_name')->nullable()->after('is_milestone');
            $table->text('version_notes')->nullable()->after('milestone_name');
            $table->string('content_hash', 64)->nullable()->after('version_notes');

            $table->index(['document_id', 'is_auto_save', 'created_at']);
            $table->index(['document_id', 'is_milestone', 'created_at']);
            $table->index(['content_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_versions', function (Blueprint $table) {
            $table->dropIndex(['document_id', 'is_auto_save', 'created_at']);
            $table->dropIndex(['document_id', 'is_milestone', 'created_at']);
            $table->dropIndex(['content_hash']);
            $table->dropColumn([
                'is_milestone',
                'milestone_name',
                'version_notes',
                'content_hash',
            ]);
        });
    }
};
