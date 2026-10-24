<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema, DB};
return new class extends Migration {
    public function up(): void
    {
        Schema::table('services', function (Blueprint $t) {
            $t->unsignedBigInteger('ticket_id')->nullable()->change();
            $t->unsignedBigInteger('technician_id')->nullable()->change();
        });
        Schema::create('ninox_service_imports', function (Blueprint $t) {
            $t->id(); $t->foreignId('service_id')->unique()->constrained('services')->cascadeOnDelete();
            $t->string('ninox_table', 20); $t->string('ninox_id', 40); $t->string('source_hash', 64);
            $t->longText('payload'); $t->timestamps(); $t->unique(['ninox_table', 'ninox_id']);
        });
        Schema::create('service_documents', function (Blueprint $t) {
            $t->id(); $t->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $t->string('kind', 30); $t->string('name'); $t->string('sha256', 64);
            $t->string('status', 20)->default('missing'); $t->string('path')->nullable();
            $t->timestamps(); $t->unique(['service_id', 'kind', 'sha256']);
        });
    }
    public function down(): void
    {
        if (DB::table('services')->whereNull('ticket_id')->orWhereNull('technician_id')->exists()) {
            throw new LogicException('Hay bitácoras históricas sin relaciones. No se revierte esta migración con datos pendientes.');
        }
        Schema::dropIfExists('service_documents'); Schema::dropIfExists('ninox_service_imports');
        Schema::table('services', function (Blueprint $t) {
            $t->unsignedBigInteger('ticket_id')->nullable(false)->change();
            $t->unsignedBigInteger('technician_id')->nullable(false)->change();
        });
    }
};
