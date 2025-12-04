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
}
