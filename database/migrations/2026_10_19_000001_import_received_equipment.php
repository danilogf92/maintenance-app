<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema,DB};
return new class extends Migration {
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $t) {
            $t->unsignedBigInteger('client_id')->nullable()->change();
            $t->unsignedBigInteger('institution_id')->nullable()->change();
        });
        Schema::create('received_equipment', function (Blueprint $t) {
            $t->id(); $t->foreignId('equipment_id')->unique()->constrained('equipment')->restrictOnDelete();
            $t->string('source_table',20); $t->string('source_id',40); $t->string('source_hash',64); $t->longText('payload');
            $t->timestamps(); $t->unique(['source_table','source_id']);
        });
    }
    public function down(): void
    {
        if (DB::table('received_equipment')->exists() || DB::table('equipment')->whereNull('client_id')->orWhereNull('institution_id')->exists()) {
            throw new LogicException('Hay equipos recibidos o relaciones pendientes. No se revierte esta migración con datos.');
        }
        Schema::dropIfExists('received_equipment');
        Schema::table('equipment', function (Blueprint $t) {
            $t->unsignedBigInteger('client_id')->nullable(false)->change(); $t->unsignedBigInteger('institution_id')->nullable(false)->change();
        });
    }
};
