<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operation_record_creations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('module', 40);
            $table->uuid('token');
            $table->unsignedBigInteger('record_id')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'module', 'token']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_record_creations');
    }
};
