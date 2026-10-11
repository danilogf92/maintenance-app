<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('institutions', function (Blueprint $table) {
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 40)->nullable();
            $table->string('city')->nullable();
        });
    }
    public function down(): void {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn(['contact_email', 'contact_phone', 'city']);
        });
    }
};
