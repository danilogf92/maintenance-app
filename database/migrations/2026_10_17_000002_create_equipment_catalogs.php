<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['manufacturers', 'brands', 'equipment_models'] as $name) {
            if (Schema::hasTable($name)) { continue; }
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id(); $table->string('code', 30)->unique(); $table->string('name', 160);
                $table->string('name_key', 64)->unique(); $table->boolean('active')->default(true);
                $table->text('notes')->nullable(); $table->unsignedInteger('lock_version')->default(1);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                if ($name === 'manufacturers') {
                    $table->string('country', 100)->nullable(); $table->string('website', 255)->nullable();
                    $table->string('logo_path')->nullable();
                } elseif ($name === 'brands') {
                    $table->foreignId('manufacturer_id')->nullable()->constrained('manufacturers')->restrictOnDelete();
                } else {
                    $table->foreignId('brand_id')->nullable()->constrained('brands')->restrictOnDelete();
                }
                $table->timestamps();
            });
        }
        foreach (['brand_id' => 'brands', 'equipment_model_id' => 'equipment_models'] as $column => $parent) {
            if (!Schema::hasColumn('equipment', $column)) {
                Schema::table('equipment', fn (Blueprint $table) => $table->foreignId($column)->nullable()->constrained($parent)->restrictOnDelete());
            }
        }
        app(\App\Modules\Catalogs\Actions\BackfillEquipmentCatalogs::class)->execute();
    }
    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropConstrainedForeignId('equipment_model_id'); $table->dropConstrainedForeignId('brand_id');
        });
        foreach (['equipment_models', 'brands', 'manufacturers'] as $name) { Schema::dropIfExists($name); }
    }
};
