<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('pendiente_etiqueta', function (Blueprint $table) {
        $table->foreignId('pendiente_id')
            ->constrained('pendientes')
            ->cascadeOnDelete();

        $table->foreignId('etiqueta_id')
            ->constrained('etiquetas')
            ->cascadeOnDelete();

        $table->string('prioridad', 20);
        $table->date('fecha_asignacion');

        $table->timestamps();

        $table->primary([
            'pendiente_id',
            'etiqueta_id'
        ]);
    });
}

public function down(): void
{
    Schema::dropIfExists('pendiente_etiqueta');
}
};
