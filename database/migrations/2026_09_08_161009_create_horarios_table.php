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
        Schema::create('horarios', function (Blueprint $table) {
        $table->id();

        $table->foreignId('usuario_id')
            ->constrained('usuarios')
            ->cascadeOnDelete();

        $table->foreignId('curso_id')
            ->constrained('cursos')
            ->cascadeOnDelete();

        $table->string('dia_semana', 15);
        $table->time('hora_inicio');
        $table->time('hora_fin');
        $table->string('salon', 50)->nullable();

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
