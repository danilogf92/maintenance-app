<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_plans', function (Blueprint $t) {
            $t->id(); $t->string('code', 30)->unique(); $t->string('name', 160);
            $t->foreignId('equipment_id')->constrained('equipment')->restrictOnDelete();
            $t->foreignId('technician_id')->nullable()->constrained('users')->restrictOnDelete();
            $t->string('service_type', 120); $t->date('first_date'); $t->date('end_date')->nullable();
            $t->unsignedSmallInteger('interval_value')->default(3); $t->string('interval_unit', 10)->default('months');
            $t->string('contract_reference', 160)->nullable(); $t->text('notes')->nullable();
            $t->unsignedInteger('schedule_version')->default(0); $t->boolean('active')->default(true); $t->unsignedInteger('lock_version')->default(1);
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $t->timestamps();
        });
        Schema::create('maintenance_visits', function (Blueprint $t) {
            $t->id(); $t->foreignId('maintenance_plan_id')->constrained()->restrictOnDelete();
            $t->unsignedInteger('sequence'); $t->date('scheduled_for')->index();
            $t->foreignId('technician_id')->nullable()->constrained('users')->restrictOnDelete();
            $t->string('status', 20)->default('planned'); $t->text('notes')->nullable();
            $t->unsignedInteger('lock_version')->default(1); $t->timestamps();
            $t->unique(['maintenance_plan_id', 'sequence']);
        });
        Schema::table('tickets', function (Blueprint $t) {
            $t->string('origin', 20)->default('legacy')->index();
            $t->foreignId('requester_contact_id')->nullable()->constrained('contacts')->restrictOnDelete();
            $t->string('reported_by', 160)->nullable();
            $t->foreignId('maintenance_visit_id')->nullable()->unique()->constrained('maintenance_visits')->restrictOnDelete();
        });
    }
    public function down(): void
    {
        Schema::table('tickets', fn (Blueprint $t) => $t->dropForeign(['maintenance_visit_id']));
        Schema::table('tickets', fn (Blueprint $t) => $t->dropUnique(['maintenance_visit_id']));
        Schema::table('tickets', fn (Blueprint $t) => $t->dropForeign(['requester_contact_id']));
        Schema::table('tickets', fn (Blueprint $t) => $t->dropIndex(['origin']));
        Schema::table('tickets', fn (Blueprint $t) => $t->dropColumn(['maintenance_visit_id', 'requester_contact_id', 'origin', 'reported_by']));
        Schema::dropIfExists('maintenance_visits'); Schema::dropIfExists('maintenance_plans');
    }
};
