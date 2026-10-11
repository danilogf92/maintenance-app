<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operation_view_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('module', 40);
            $table->foreignId('operation_view_id')->constrained('operation_views')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'module']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_view_preferences');
    }
};
