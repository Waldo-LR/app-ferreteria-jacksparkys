<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tproductos', function (Blueprint $table): void {
            $table->foreignId('id_usuario_baja')->nullable()->constrained('tusuarios', 'id_usuario')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tproductos', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('id_usuario_baja');
        });
    }
};
