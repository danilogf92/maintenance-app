<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('contacts')) {
            Schema::create('contacts', function (Blueprint $table) {
                $table->id(); $table->string('code', 30)->unique(); $table->string('name', 160)->index();
                $table->string('position', 120)->nullable(); $table->string('department', 120)->nullable();
                $table->string('email', 160)->nullable(); $table->string('phone', 60)->nullable(); $table->string('extension', 20)->nullable();
                $table->text('notes')->nullable(); $table->boolean('active')->default(true); $table->unsignedInteger('lock_version')->default(1);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
            });
        }
        if (!Schema::hasTable('contact_links')) {
            Schema::create('contact_links', function (Blueprint $table) {
                $table->id(); $table->foreignId('contact_id')->constrained('contacts')->restrictOnDelete();
                $table->foreignId('client_id')->nullable()->constrained('clients')->restrictOnDelete();
                $table->foreignId('institution_id')->nullable()->constrained('institutions')->restrictOnDelete();
                $table->string('category', 30)->default('other'); $table->boolean('active')->default(true);
                $table->boolean('is_primary')->default(false); $table->string('primary_key', 50)->nullable()->unique();
                $table->unsignedInteger('lock_version')->default(1); $table->timestamps();
                $table->unique(['contact_id', 'client_id']); $table->unique(['contact_id', 'institution_id']);
            });
        }
    }
    public function down(): void { Schema::dropIfExists('contact_links'); Schema::dropIfExists('contacts'); }
};
