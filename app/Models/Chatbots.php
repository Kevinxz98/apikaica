<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chatbots extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'nombre', 'categoria', 'idioma', 'avatar',
        'nombreEmpresa', 'sitioWeb', 'descripcionEmpresa',
        'horarioAtencion', 'informacionAdicional',
        'estilo', 'nivelTecnico', 'usoEmojis',
        'mensajeBienvenida', 'mensajeNoDisponible', 'mensajeAusencia',
        'respuestasRapidas',
        'color', 'posicion', 'mostrarAvatar', 'sonidoNotificacion',
        'tamanoWidget',
        'objetivoPrincipal', 'preguntasFrecuentes', 'temasExcluidos',
        'datosCapturar',
        'estadoActivacion',
    ];

    protected $casts = [
        'respuestasRapidas' => 'array',
        'datosCapturar' => 'array',
        'mostrarAvatar' => 'boolean',
        'sonidoNotificacion' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function usageLogs()
    {
        return $this->hasMany(chatbot_usage_logs::class);
    }

    public function stats()
    {
        return $this->hasOne(chatbot_stats::class);
    }

    public function latestLogs($limit = 10)
    {
        return $this->usageLogs()->latest()->limit($limit)->get();
    }
}
