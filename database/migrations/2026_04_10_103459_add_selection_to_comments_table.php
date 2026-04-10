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
        Schema::table('comments', function (Blueprint $table) {
            $table->string('selection_text', 500)->nullable()->after('content');
            $table->integer('selection_start')->nullable()->after('selection_text');
            $table->integer('selection_end')->nullable()->after('selection_start');
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn(['selection_text', 'selection_start', 'selection_end']);
        });
    }
};
