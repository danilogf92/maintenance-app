<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $t) {
            $this->identity($t); $t->string('name'); $t->string('tax_id')->nullable()->index();
            $t->string('email')->nullable(); $t->string('phone')->nullable();
            $t->string('address')->nullable(); $t->string('city')->nullable(); $t->boolean('active')->default(true);
        });
        Schema::create('equipment', function (Blueprint $t) {
            $this->identity($t); $t->foreignId('client_id')->constrained()->restrictOnDelete();
            $t->foreignId('institution_id')->constrained()->restrictOnDelete();
            $t->string('name'); $t->string('brand')->nullable(); $t->string('model')->nullable();
            $t->string('serial')->nullable()->index(); $t->string('location')->nullable(); $t->string('status')->default('operational');
        });
        Schema::create('tickets', function (Blueprint $t) {
            $this->identity($t); $t->foreignId('equipment_id')->constrained('equipment')->restrictOnDelete();
            $t->foreignId('technician_id')->constrained('users')->restrictOnDelete();
            $t->string('subject'); $t->text('description')->nullable(); $t->string('priority')->default('normal');
            $t->string('status')->default('open')->index(); $t->dateTime('opened_at');
        });
        Schema::create('services', function (Blueprint $t) {
            $this->identity($t); $t->foreignId('ticket_id')->constrained()->restrictOnDelete();
            $t->foreignId('technician_id')->constrained('users')->restrictOnDelete(); $t->date('service_date');
            $t->string('service_type'); $t->string('status')->default('draft')->index();
            $t->text('summary')->nullable(); $t->text('recommendations')->nullable();
        });
        Schema::create('activities', function (Blueprint $t) {
            $this->identity($t); $t->foreignId('service_id')->constrained()->restrictOnDelete();
            $t->text('description'); $t->text('result')->nullable(); $t->json('photos')->nullable();
        });
        Schema::create('technical_reports', function (Blueprint $t) {
            $this->identity($t); $t->foreignId('service_id')->constrained()->restrictOnDelete();
            $t->foreignId('report_template_id')->constrained()->restrictOnDelete(); $t->string('title');
            $t->unsignedInteger('revision')->default(1); $t->longText('snapshot');
        });
        Schema::table('report_generations', function (Blueprint $t) {
            $t->foreignId('technical_report_id')->nullable()->constrained()->restrictOnDelete();
            $t->unsignedInteger('report_revision')->nullable();
        });
    }

    private function identity(Blueprint $t): void
    {
        $t->id(); $t->string('code', 30)->unique(); $t->unsignedInteger('lock_version')->default(0);
        $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $t->timestamps();
    }

    public function down(): void
    {
        Schema::table('report_generations', function (Blueprint $t) { $t->dropConstrainedForeignId('technical_report_id'); $t->dropColumn('report_revision'); });
        foreach (['technical_reports', 'activities', 'services', 'tickets', 'equipment', 'clients'] as $table) { Schema::dropIfExists($table); }
    }
};
