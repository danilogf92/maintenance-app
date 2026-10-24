<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_protocols', function (Blueprint $table) {
            $table->id(); $table->string('source_id', 20)->unique();
            $table->string('code')->nullable()->index(); $table->string('name')->nullable();
            $table->text('description')->nullable(); $table->string('version')->nullable();
            $table->string('type')->nullable(); $table->string('model_info')->nullable();
            $table->boolean('is_obsolete')->nullable(); $table->char('source_hash', 64); $table->longText('source_payload');
            $table->timestamps();
        });
        Schema::create('maintenance_protocol_steps', function (Blueprint $table) {
            $table->id(); $table->string('source_id', 20)->unique(); $table->string('parent_source_id', 20)->nullable()->index();
            $table->foreignId('maintenance_protocol_id')->nullable()->constrained('maintenance_protocols')->restrictOnDelete();
            $table->unsignedInteger('position')->nullable(); $table->integer('step_number')->nullable();
            $table->text('instruction')->nullable(); $table->text('report_text')->nullable();
            $table->string('type')->nullable(); $table->string('range')->nullable(); $table->boolean('include_in_report');
            $table->char('source_hash', 64); $table->longText('source_payload'); $table->timestamps();
        });
        Schema::create('maintenance_protocol_files', function (Blueprint $table) {
            $table->id(); $table->foreignId('protocol_step_id')->constrained('maintenance_protocol_steps')->restrictOnDelete();
            $table->char('sha256', 64); $table->string('name'); $table->string('path'); $table->string('mime', 80);
            $table->unique(['protocol_step_id', 'sha256']); $table->timestamps();
        });
        Schema::create('service_protocol_applications', function (Blueprint $table) {
            $table->id(); $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->foreignId('maintenance_protocol_id')->constrained('maintenance_protocols')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->longText('snapshot'); $table->timestamps();
            $table->unique(['service_id', 'maintenance_protocol_id'], 'service_protocol_once');
        });
        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('protocol_application_id')->nullable()->constrained('service_protocol_applications')->restrictOnDelete();
            $table->foreignId('protocol_step_id')->nullable()->constrained('maintenance_protocol_steps')->restrictOnDelete();
            $table->longText('protocol_snapshot')->nullable(); $table->boolean('protocol_include_in_report')->default(true);
        });
    }
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('protocol_application_id'); $table->dropConstrainedForeignId('protocol_step_id');
            $table->dropColumn(['protocol_snapshot', 'protocol_include_in_report']);
        });
        Schema::dropIfExists('service_protocol_applications'); Schema::dropIfExists('maintenance_protocol_files');
        Schema::dropIfExists('maintenance_protocol_steps'); Schema::dropIfExists('maintenance_protocols');
    }
};
