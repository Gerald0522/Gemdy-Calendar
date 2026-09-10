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
            Schema::create('pendientes', function (Blueprint $table) {
        $table->id();

        $table->foreignId('usuario_id')
            ->constrained('usuarios')
            ->cascadeOnDelete();

        $table->foreignId('curso_id')
            ->nullable()
            ->constrained('cursos')
            ->nullOnDelete();

        $table->string('titulo', 150);

        $table->text('descripcion')->nullable();

        $table->string('estado', 20)
            ->default('pendiente')
            ->index();

        $table->date('fecha_limite')->nullable();

        $table->time('hora_pendiente')->nullable();

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendientes');
    }
};
