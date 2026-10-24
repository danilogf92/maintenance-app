<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_import_runs', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->string('status')->index();
            $t->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('package_id', 32); $t->json('summary')->nullable(); $t->json('issues')->nullable();
            $t->timestamp('finished_at')->nullable(); $t->timestamps();
        });
        Schema::create('data_source_records', function (Blueprint $t) {
            $t->id(); $t->string('entity', 40); $t->string('source_table', 20); $t->string('source_id', 40);
            $t->unsignedBigInteger('record_id'); $t->string('source_hash', 64); $t->longText('payload'); $t->timestamps();
            $t->unique(['entity', 'source_table', 'source_id']);
        });
        Schema::table('institutions', fn (Blueprint $t) => $t->text('address')->nullable());
    }
    public function down(): void
    {
        Schema::table('institutions', fn (Blueprint $t) => $t->dropColumn('address'));
        Schema::dropIfExists('data_source_records'); Schema::dropIfExists('data_import_runs');
    }
};
