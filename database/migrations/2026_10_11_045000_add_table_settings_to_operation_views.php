<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operation_views', function (Blueprint $table) {
            $table->json('table_settings')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('operation_views', fn (Blueprint $table) => $table->dropColumn('table_settings'));
    }
};
