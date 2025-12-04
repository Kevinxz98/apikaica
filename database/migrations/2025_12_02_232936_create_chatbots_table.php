<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('chatbots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nombre');
            $table->string('categoria');
            $table->string('idioma')->default('es');
            $table->text('avatar')->nullable(); // base64 o URL

            // Empresa
            $table->string('nombreEmpresa');
            $table->string('sitioWeb')->nullable();
            $table->text('descripcionEmpresa')->nullable();
            $table->string('horarioAtencion')->nullable();
            $table->text('informacionAdicional')->nullable();

            // Estilo
            $table->string('estilo');
            $table->integer('nivelTecnico')->default(50);
            $table->boolean('usoEmojis')->default(true);

            // Mensajes
            $table->text('mensajeBienvenida');
            $table->text('mensajeNoDisponible');
            $table->text('mensajeAusencia');
            $table->json('respuestasRapidas')->nullable();

            // Apariencia y config del widget
            $table->string('color');
            $table->string('posicion')->default('bottom-right');
            $table->boolean('mostrarAvatar')->default(false);
            $table->boolean('sonidoNotificacion')->default(false);
            $table->string('tamanoWidget')->nullable();

            // Objetivos
            $table->string('objetivoPrincipal');
            $table->text('preguntasFrecuentes')->nullable();
            $table->text('temasExcluidos')->nullable();
            $table->json('datosCapturar')->nullable();

            $table->string('estadoActivacion');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbots');
    }
};
