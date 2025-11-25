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
        Schema::create('chatbot_instances', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            // Relación con usuario
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Relación con servicios/plantillas de chatbot
            $table->unsignedBigInteger('service_id')->nullable();
            $table->foreign('service_id')->references('id')->on('services')->onDelete('set null');

            // Identificador único para el embed
            $table->string('client_slug', 50)->unique();

            // Información del chatbot
            $table->string('name', 100);
            $table->text('welcome_message')->nullable();
            $table->string('color', 20)->nullable();
            $table->string('avatar', 255)->nullable();

            // Conocimiento
            $table->enum('knowledge_mode', ['manual', 'auto', 'mixed'])->default('manual');
            $table->longText('knowledge_manual')->nullable();

            // Estado
            $table->enum('status', ['active', 'inactive'])->default('active');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_instances');
    }
};
