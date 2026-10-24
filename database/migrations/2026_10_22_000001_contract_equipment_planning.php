<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_contracts', function (Blueprint $t) {
            $t->id();
            $t->string('code', 30)->unique();
            $t->string('number', 100)->unique();
            $t->string('name', 160);
            $t->foreignId('client_id')->nullable()->constrained('clients')->restrictOnDelete();
            $t->boolean('active')->default(true);
            $t->text('notes')->nullable();
            $t->unsignedInteger('lock_version')->default(1);
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
        Schema::create('contract_equipment_coverages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('maintenance_contract_id')->constrained()->restrictOnDelete();
            $t->foreignId('equipment_id')->constrained('equipment')->restrictOnDelete();
            $t->string('first_month', 7);
            $t->string('end_month', 7);
            $t->text('requirements')->nullable();
            $t->unsignedInteger('lock_version')->default(1);
            $t->timestamps();
            $t->unique(['maintenance_contract_id', 'equipment_id'], 'contract_equipment_unique');
        });
        Schema::table('maintenance_plans', function (Blueprint $t) {
            $t->foreignId('contract_coverage_id')->nullable()->index()->constrained('contract_equipment_coverages')->restrictOnDelete();
            $t->string('schedule_precision', 10)->default('month');
            $t->unsignedInteger('expected_visits')->nullable();
        });
        Schema::table('maintenance_visits', fn (Blueprint $t) => $t->string('schedule_precision', 10)->default('month'));
        Schema::table('services', fn (Blueprint $t) => $t->index(['ticket_id', 'status'], 'services_ticket_status_index'));
    }

    public function down(): void
    {
        Schema::table('services', fn (Blueprint $t) => $t->dropIndex('services_ticket_status_index'));
        Schema::table('maintenance_visits', fn (Blueprint $t) => $t->dropColumn('schedule_precision'));
        Schema::table('maintenance_plans', function (Blueprint $t) {
            $t->dropForeign(['contract_coverage_id']);
            $t->dropIndex(['contract_coverage_id']);
            $t->dropColumn(['contract_coverage_id', 'schedule_precision', 'expected_visits']);
        });
        Schema::dropIfExists('contract_equipment_coverages');
        Schema::dropIfExists('maintenance_contracts');
    }
};
