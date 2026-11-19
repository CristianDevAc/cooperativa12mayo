<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('egresos', function (Blueprint $table) {

            $table->timestampTz('anulado_en')
                ->nullable();

            $table->string('motivo_anulacion', 500)
                ->nullable();

            $table->foreignId('anulado_por')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('egresos', function (Blueprint $table) {

            $table->dropConstrainedForeignId('anulado_por');

            $table->dropColumn([
                'anulado_en',
                'motivo_anulacion',
            ]);
        });
    }
};