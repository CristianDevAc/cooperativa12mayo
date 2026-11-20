
<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * La tabla solicitudes_reincorporacion ya fue creada
     * por la migración:
     *
     * 2026_11_03_001056_add_unique_cobro_unico_to_recibos_otros_ingresos
     *
     * Esta migración se conserva para mantener el
     * historial y evitar crear la tabla dos veces.
     */
    public function up(): void
    {
        // No realizar cambios: la tabla ya existe.
    }

    /**
     * No eliminar la tabla al revertir esta migración.
     * La migración anterior es la que la creó.
     */
    public function down(): void
    {
        // No realizar cambios.
    }
};
