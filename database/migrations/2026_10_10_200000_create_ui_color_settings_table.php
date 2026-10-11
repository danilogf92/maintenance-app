<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ui_color_settings', function (Blueprint $table) {
            $table->id();
            $table->string('primary', 7)->default('#2563EB');
            $table->string('success', 7)->default('#16A34A');
            $table->string('danger', 7)->default('#DC2626');
            $table->string('secondary', 7)->default('#64748B');
            $table->timestamps();
        });
        DB::table('ui_color_settings')->insert([
            'id' => 1, 'primary' => '#2563EB', 'success' => '#16A34A',
            'danger' => '#DC2626', 'secondary' => '#64748B',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
    public function down(): void { Schema::dropIfExists('ui_color_settings'); }
};
