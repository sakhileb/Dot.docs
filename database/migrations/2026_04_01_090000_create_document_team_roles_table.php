<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_team_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['viewer', 'commenter', 'editor', 'reviewer', 'admin'])->default('viewer');
            $table->json('permissions')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['document_id', 'team_id']);
            $table->index('team_id');
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_team_roles');
    }
};
