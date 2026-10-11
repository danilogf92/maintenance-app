<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('operation_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('module', 40);
            $table->string('name', 100);
            $table->string('filter_value', 100)->default('');
            $table->json('columns');
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['user_id', 'module', 'sort_order']);
        });
    }

    public function down(): void { Schema::dropIfExists('operation_views'); }
};
