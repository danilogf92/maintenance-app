<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema, DB};
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_plans', function (Blueprint $t) {
            $t->string('first_month', 7)->nullable(); $t->string('end_month', 7)->nullable(); $t->unsignedSmallInteger('interval_months')->nullable();
        });
        Schema::table('maintenance_visits', fn (Blueprint $t) => $t->string('scheduled_month', 7)->nullable()->index());
        DB::table('maintenance_plans')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $months = match ($row->interval_unit) { 'months' => $row->interval_value, 'years' => $row->interval_value * 12, default => null };
                DB::table('maintenance_plans')->where('id', $row->id)->update(['first_month' => substr($row->first_date, 0, 7),
                    'end_month' => $row->end_date ? substr($row->end_date, 0, 7) : null, 'interval_months' => $months]);
            }
        });
        DB::table('maintenance_visits')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) { DB::table('maintenance_visits')->where('id', $row->id)->update(['scheduled_month' => substr($row->scheduled_for, 0, 7)]); }
        });
    }
    public function down(): void
    {
        Schema::table('maintenance_visits', fn (Blueprint $t) => $t->dropIndex(['scheduled_month']));
        Schema::table('maintenance_visits', fn (Blueprint $t) => $t->dropColumn('scheduled_month'));
        Schema::table('maintenance_plans', fn (Blueprint $t) => $t->dropColumn(['first_month', 'end_month', 'interval_months']));
    }
};
