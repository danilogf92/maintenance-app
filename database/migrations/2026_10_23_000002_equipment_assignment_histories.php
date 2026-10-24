<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        foreach (['equipment_client_histories' => ['client_id', 'clients'], 'equipment_institution_histories' => ['institution_id', 'institutions']] as $table => [$column, $parent]) {
            Schema::create($table, function (Blueprint $t) use ($column, $parent) {
                $t->id(); $t->string('source_id', 40)->unique(); $t->string('source_hash', 64); $t->longText('payload');
                $t->foreignId('equipment_id')->nullable()->constrained('equipment')->restrictOnDelete();
                $t->foreignId($column)->nullable()->constrained($parent)->restrictOnDelete();
                $t->dateTime('assigned_at')->nullable(); $t->boolean('date_only')->default(true); $t->json('issues'); $t->timestamps();
            });
        }
        Schema::table('data_import_runs', function (Blueprint $t) {
            $t->string('operation')->default('catalogs'); $t->json('link_results')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('data_import_runs', fn (Blueprint $t) => $t->dropColumn(['operation', 'link_results']));
        Schema::dropIfExists('equipment_client_histories'); Schema::dropIfExists('equipment_institution_histories');
    }
};
