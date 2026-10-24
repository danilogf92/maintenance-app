<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema,DB};
return new class extends Migration {
    public function up(): void
    {
        Schema::create('field_engineers',function (Blueprint $t) {
            $t->id(); $t->string('code',30)->unique(); $t->string('name',160); $t->string('initials',30)->nullable();
            $t->string('country',100)->nullable(); $t->string('city',100)->nullable();
            $t->string('email',160)->nullable(); $t->string('phone',60)->nullable();
            $t->string('source_table',20); $t->string('source_id',40); $t->string('source_hash',64); $t->longText('source_payload');
            $t->timestamps(); $t->unique(['source_table','source_id']);
        });
        Schema::create('ticket_field_engineer',function (Blueprint $t) {
            $t->id(); $t->foreignId('ticket_id')->constrained()->restrictOnDelete();
            $t->foreignId('field_engineer_id')->constrained()->restrictOnDelete();
            $t->string('source_assignment_id',40); $t->timestamps(); $t->unique(['ticket_id','source_assignment_id']);
        });
        Schema::table('services',function (Blueprint $t) {
            $t->foreignId('field_engineer_id')->nullable()->constrained()->restrictOnDelete(); $t->string('field_engineer_basis',40)->nullable();
        });
    }
    public function down(): void
    {
        if (DB::table('field_engineers')->exists() || DB::table('ticket_field_engineer')->exists() || DB::table('services')->whereNotNull('field_engineer_id')->exists()) {
            throw new LogicException('Hay ingenieros o vínculos recibidos. No se revierte esta migración con datos.');
        }
        Schema::table('services',function (Blueprint $t) { $t->dropConstrainedForeignId('field_engineer_id'); $t->dropColumn('field_engineer_basis'); });
        Schema::dropIfExists('ticket_field_engineer'); Schema::dropIfExists('field_engineers');
    }
};
