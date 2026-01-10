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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Información básica del perfil
            $table->string('profile_image')->nullable()->comment('Ruta de la imagen de perfil');
            $table->string('profile_image_bg')->nullable()->comment('Ruta de la imagen bg de perfil');
            $table->string('phone_number', 20)->nullable()->comment('Número de teléfono');
            $table->integer('age')->nullable()->comment('Edad del usuario');
            $table->text('bio')->nullable()->comment('Biografía del usuario');
            $table->string('language', 10)->default('es')->comment('Idioma preferido (código ISO)');
            $table->string('timezone', 50)->default('UTC')->comment('Zona horaria del usuario');
            
            // Configuración de seguridad
            $table->boolean('is_two_factor_enabled')->default(false)->comment('Verificación en dos pasos');
            $table->boolean('require_password_for_changes')->default(false)->comment('Requerir contraseña para cambios');
            
            // Configuración de notificaciones
            $table->boolean('in_app_notifications')->default(true)->comment('Notificaciones en la app');
            $table->boolean('email_notifications')->default(true)->comment('Notificaciones por email');
            $table->boolean('push_notifications')->default(false)->comment('Notificaciones push');
            $table->boolean('sms_notifications')->default(true)->comment('Notificaciones por SMS');
            
            // Estado
            $table->boolean('is_active')->default(true)->comment('Cuenta activa');
            
            // Timestamps
            $table->timestamps();
            
            // Índices
            $table->index('user_id');
            $table->index('language');
            $table->index('timezone');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
