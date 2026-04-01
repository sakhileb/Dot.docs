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
        Schema::table('document_shares', function (Blueprint $table) {
            $table->boolean('is_public_link')->default(false)->after('permission');
            $table->string('shared_with_email')->nullable()->after('shared_with_user_id');
            $table->string('allowed_domain')->nullable()->after('shared_with_email');
            $table->unsignedInteger('views_count')->default(0)->after('last_accessed_at');
            $table->unsignedInteger('edits_count')->default(0)->after('views_count');
            $table->unsignedInteger('link_access_count')->default(0)->after('edits_count');

            $table->index(['is_public_link', 'status']);
            $table->index(['shared_with_email', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_shares', function (Blueprint $table) {
            $table->dropIndex(['is_public_link', 'status']);
            $table->dropIndex(['shared_with_email', 'status']);

            $table->dropColumn([
                'is_public_link',
                'shared_with_email',
                'allowed_domain',
                'views_count',
                'edits_count',
                'link_access_count',
            ]);
        });
    }
};
