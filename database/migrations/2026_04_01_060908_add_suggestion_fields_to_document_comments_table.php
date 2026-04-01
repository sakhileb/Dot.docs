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
        Schema::table('document_comments', function (Blueprint $table) {
            $table->string('type')->default('comment')->after('body'); // comment, suggestion, mention
            $table->string('suggestion_type')->nullable()->after('type'); // addition, deletion, modification
            $table->text('suggested_text')->nullable()->after('suggestion_type');
            $table->integer('selection_start')->nullable()->after('suggested_text');
            $table->integer('selection_end')->nullable()->after('selection_start');
            $table->boolean('suggestion_accepted')->default(false)->after('selection_end');
            $table->foreignId('accepted_by_user_id')->nullable()->after('suggestion_accepted')->constrained('users')->onDelete('set null');
            $table->timestamp('accepted_at')->nullable()->after('accepted_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_comments', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'suggestion_type',
                'suggested_text',
                'selection_start',
                'selection_end',
                'suggestion_accepted',
                'accepted_by_user_id',
                'accepted_at',
            ]);
        });
    }
};
