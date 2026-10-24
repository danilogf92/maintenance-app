<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema, DB};
return new class extends Migration {
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $t) { $t->string('distributor')->nullable(); $t->string('origin')->nullable(); });
        Schema::table('equipment_models', function (Blueprint $t) { $t->text('description')->nullable(); $t->unsignedSmallInteger('visits_recommended_per_year')->nullable(); });
        Schema::create('ninox_catalog_imports', function (Blueprint $t) {
            $t->id(); $t->string('ninox_table', 20); $t->string('ninox_id', 40); $t->string('catalog', 30);
            $t->unsignedBigInteger('record_id'); $t->string('source_hash', 64); $t->longText('payload');
            $t->string('brand_basis')->nullable(); $t->string('source_brand_id', 40)->nullable(); $t->timestamps();
            $t->unique(['ninox_table', 'ninox_id']); $t->unique(['catalog', 'record_id']);
        });
        Schema::create('ninox_catalog_assets', function (Blueprint $t) {
            $t->id(); $t->foreignId('ninox_catalog_import_id')->constrained('ninox_catalog_imports')->cascadeOnDelete();
            $t->string('name'); $t->string('sha256', 64); $t->string('mime', 50)->nullable();
            $t->string('path')->nullable(); $t->string('status', 20); $t->timestamps();
            $t->unique(['ninox_catalog_import_id', 'sha256'], 'ninox_catalog_asset_unique');
        });
    }
    public function down(): void
    {
        if (DB::table('ninox_catalog_imports')->exists()) {
            throw new LogicException('Hay catálogos importados. No se revierte esta migración con datos históricos.');
        }
        Schema::dropIfExists('ninox_catalog_assets'); Schema::dropIfExists('ninox_catalog_imports');
        Schema::table('brands', fn (Blueprint $t) => $t->dropColumn(['distributor', 'origin']));
        Schema::table('equipment_models', fn (Blueprint $t) => $t->dropColumn(['description', 'visits_recommended_per_year']));
    }
};
