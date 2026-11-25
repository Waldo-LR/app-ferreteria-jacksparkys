<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tconfiguracion', function (Blueprint $table) {
            $table->string('email_soporte', 100)->nullable();
            $table->string('central_soporte', 60)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tconfiguracion', function (Blueprint $table) {
            $table->dropColumn(['email_soporte', 'central_soporte']);
        });
    }
};
