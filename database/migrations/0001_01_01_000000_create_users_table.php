<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('code')->unique();
            $t->string('type')->nullable();
            $t->timestamps();
        });
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->foreignId('institution_id')->constrained()->restrictOnDelete();
            $t->string('name');
            $t->string('email')->unique();
            $t->string('identification')->nullable();
            $t->string('phone')->nullable();
            $t->string('job_title')->nullable();
            $t->string('password');
            $t->boolean('is_active')->default(true);
            $t->boolean('can_access_admin')->default(false);
            $t->timestamp('email_verified_at')->nullable();
            $t->rememberToken();
            $t->timestamps();
        });
        Schema::create('password_reset_tokens', function (Blueprint $t) {
            $t->string('email')->primary();
            $t->string('token');
            $t->timestamp('created_at')->nullable();
        });
        Schema::create('sessions', function (Blueprint $t) {
            $t->string('id')->primary();
            $t->foreignId('user_id')->nullable()->index();
            $t->string('ip_address', 45)->nullable();
            $t->text('user_agent')->nullable();
            $t->longText('payload');
            $t->integer('last_activity')->index();
        });
        Schema::create('brand_settings', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('logo_path')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['brand_settings', 'sessions', 'password_reset_tokens', 'users', 'institutions'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
