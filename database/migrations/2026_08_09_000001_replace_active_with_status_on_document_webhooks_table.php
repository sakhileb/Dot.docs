<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_webhooks', function (Blueprint $table) {
            $table->string('status')->default('active')->after('secret');
            $table->text('rejected_reason')->nullable()->after('status');
            $table->foreignId('reviewed_by')->nullable()->after('rejected_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });

        // Backfill: any pre-existing row's boolean active=false becomes status=inactive.
        DB::table('document_webhooks')->where('active', false)->update(['status' => 'inactive']);

        Schema::table('document_webhooks', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }

    public function down(): void
    {
        Schema::table('document_webhooks', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('secret');
        });

        DB::table('document_webhooks')->where('status', '!=', 'active')->update(['active' => false]);

        Schema::table('document_webhooks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['status', 'rejected_reason', 'reviewed_at']);
        });
    }
};
