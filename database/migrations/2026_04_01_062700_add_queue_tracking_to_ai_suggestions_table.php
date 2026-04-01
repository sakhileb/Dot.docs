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
        Schema::table('ai_suggestions', function (Blueprint $table) {
            $table->string('operation')->nullable()->after('prompt');
            $table->unsignedTinyInteger('progress')->default(0)->after('status');
            $table->string('request_hash', 64)->nullable()->after('progress');
            $table->boolean('is_cached')->default(false)->after('request_hash');
            $table->timestamp('queued_at')->nullable()->after('is_cached');
            $table->timestamp('started_at')->nullable()->after('queued_at');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->json('metadata')->nullable()->after('completed_at');

            $table->index(['operation', 'created_at']);
            $table->index(['request_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_suggestions', function (Blueprint $table) {
            $table->dropIndex(['operation', 'created_at']);
            $table->dropIndex(['request_hash']);
            $table->dropColumn([
                'operation',
                'progress',
                'request_hash',
                'is_cached',
                'queued_at',
                'started_at',
                'completed_at',
                'metadata',
            ]);
        });
    }
};
