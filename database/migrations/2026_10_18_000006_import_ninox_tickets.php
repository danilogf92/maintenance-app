<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema, DB};
return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $t) {
            $t->unsignedBigInteger('equipment_id')->nullable()->change(); $t->unsignedBigInteger('technician_id')->nullable()->change();
            $t->string('ninox_status')->nullable();
        });
        Schema::create('ninox_ticket_imports', function (Blueprint $t) {
            $t->id(); $t->foreignId('ticket_id')->unique()->constrained('tickets')->cascadeOnDelete();
            $t->string('ninox_table', 20); $t->string('ninox_id', 40); $t->string('source_hash',64); $t->longText('payload');
            $t->timestamps(); $t->unique(['ninox_table','ninox_id']);
        });
    }
    public function down(): void
    {
        if (DB::table('ninox_ticket_imports')->exists() || DB::table('tickets')->whereNull('equipment_id')->orWhereNull('technician_id')->exists()) {
            throw new LogicException('Hay tickets históricos o relaciones pendientes. No se revierte esta migración con datos importados.');
        }
        Schema::dropIfExists('ninox_ticket_imports');
        Schema::table('tickets', function (Blueprint $t) {
            $t->dropColumn('ninox_status'); $t->unsignedBigInteger('equipment_id')->nullable(false)->change();
            $t->unsignedBigInteger('technician_id')->nullable(false)->change();
        });
    }
};
