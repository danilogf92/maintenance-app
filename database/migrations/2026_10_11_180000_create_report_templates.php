<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->text('description')->nullable();
            $table->json('definition'); $table->unsignedInteger('lock_version')->default(0);
            $table->unsignedBigInteger('published_version_id')->nullable(); $table->timestamps();
        });
        Schema::create('report_template_versions', function (Blueprint $table) {
            $table->id(); $table->foreignId('report_template_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number'); $table->json('definition');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps(); $table->unique(['report_template_id', 'number']);
        });
        Schema::create('report_generations', function (Blueprint $table) {
            $table->uuid('id')->primary(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('report_template_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('queued')->index(); $table->longText('snapshot');
            $table->string('error')->nullable(); $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_generations');
        Schema::dropIfExists('report_template_versions');
        Schema::dropIfExists('report_templates');
    }
};
