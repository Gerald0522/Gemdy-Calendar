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
        Schema::create('cursos', function (Blueprint $table) {
        $table->id();

        $table->foreignId('usuario_id')
            ->constrained('usuarios')
            ->cascadeOnDelete();

        $table->string('nombre', 100);
        $table->string('codigo', 20);
        $table->string('semestre', 20);
        $table->unsignedTinyInteger('creditos');

        $table->timestamps();

        $table->unique(['usuario_id', 'codigo']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
