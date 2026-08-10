<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Scoped the same way Document itself is: team-wide when team_id is
     * set, personal (per owner_id) otherwise. Uniqueness within a scope
     * (no two tags named the same in one team, or for one personal owner)
     * is enforced in TagRepository::findOrCreate() rather than a DB
     * constraint -- a composite unique index including a nullable
     * team_id doesn't reliably reject duplicate personal tags across
     * database drivers (NULL isn't equal to NULL for uniqueness purposes).
     */
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->index(['team_id', 'name']);
            $table->index(['owner_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
