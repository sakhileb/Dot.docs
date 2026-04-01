<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->index(['team_id', 'user_id', 'updated_at'], 'documents_team_user_updated_idx');
            $table->index(['team_id', 'is_archived', 'updated_at'], 'documents_team_archived_updated_idx');
        });

        Schema::table('document_comments', function (Blueprint $table) {
            $table->index(['document_id', 'parent_id', 'created_at'], 'doc_comments_doc_parent_created_idx');
        });

        Schema::table('document_shares', function (Blueprint $table) {
            $table->index(['shared_with_user_id', 'status', 'expires_at'], 'doc_shares_user_status_expires_idx');
        });

        Schema::table('document_versions', function (Blueprint $table) {
            $table->index(['document_id', 'created_at'], 'doc_versions_doc_created_idx');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(['team_id', 'created_at'], 'activity_logs_team_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('documents_team_user_updated_idx');
            $table->dropIndex('documents_team_archived_updated_idx');
        });

        Schema::table('document_comments', function (Blueprint $table) {
            $table->dropIndex('doc_comments_doc_parent_created_idx');
        });

        Schema::table('document_shares', function (Blueprint $table) {
            $table->dropIndex('doc_shares_user_status_expires_idx');
        });

        Schema::table('document_versions', function (Blueprint $table) {
            $table->dropIndex('doc_versions_doc_created_idx');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('activity_logs_team_created_idx');
        });
    }
};
