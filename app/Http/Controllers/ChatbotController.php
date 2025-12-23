<?php

namespace App\Http\Controllers;

use App\Models\Chatbots;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;


//use storage


class ChatbotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        if ($request->has('respuestasRapidas') && is_string($request->respuestasRapidas)) {
            $request->merge([
                'respuestasRapidas' => json_decode($request->respuestasRapidas, true)
            ]);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:100',
            'categoria' => 'sometimes|string|in:Ventas,Soporte,Reservas,Educación,Servicios profesionales,Otro',
            'idioma' => 'sometimes|string|in:es,en,fr,de,pt,it',
            'avatar' => 'nullable|sometimes|image|mimes:jpg,jpeg,png,gif|max:2048',
            'estilo' => 'sometimes|string|in:Formal,Casual,Divertido,Profesional,Educativo',
            'nivelTecnico' => 'sometimes|integer|min:0|max:100',
            'usoEmojis' => 'sometimes|string|in:Sí,No,Que los decida según contexto',
            'nombreEmpresa' => 'sometimes|string|max:150',
            'sitioWeb' => 'nullable|sometimes|url|max:255',
            'descripcionEmpresa' => 'sometimes|string|max:1000',
            'horarioAtencion' => 'nullable|sometimes|string|max:200',
            'informacionAdicional' => 'nullable|sometimes|string|max:500',
            'mensajeBienvenida' => 'sometimes|string|max:250',
            'mensajeNoDisponible' => 'sometimes|string|max:200',
            'mensajeAusencia' => 'sometimes|string|max:200',
            'respuestasRapidas' => 'nullable|sometimes|array',
            'respuestasRapidas.*' => 'string|max:1000',
            'color' => 'sometimes|string|regex:/^#[0-9A-F]{6}$/i',
            'posicion' => 'sometimes|string|in:bottom-right,bottom-left,top-right,top-left',
            'mostrarAvatar' => 'sometimes|boolean',
            'sonidoNotificacion' => 'sometimes|boolean',
            'tamanoWidget' => 'nullable|sometimes|string|in:pequeño,mediano,grande',
            'objetivoPrincipal' => 'sometimes|string|in:Generar ventas,Atender clientes,Reservas / agendamiento,Información rápida,FAQ,Todos los anteriores',
            'preguntasFrecuentes' => 'nullable|sometimes|string|max:1000',
            'temasExcluidos' => 'nullable|sometimes|string|max:500',
            'datosCapturar' => 'nullable|sometimes|array',
            'datosCapturar.*' => 'string|in:Nombre,Teléfono,Email,Otro campo',
            'estadoActivacion' => 'sometimes|string|in:Activar,Solo guardar,Guardar como borrador',
        ]);


        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('chatbots', 'public');
            $validated['avatar'] = $path;
        }

        try {
            $request->validate([
                'sitioWeb' => 'nullable|url|unique:chatbots,sitioWeb',
            ]);
        } catch (ValidationException $e) {
            if (isset($e->errors()['sitioWeb'])) {
                return response()->json([
                    'message' => 'Ya existe un chatbot configurado para este sitio web.'
                ], 409);
            }
            throw $e;
        }

        $statusMap = [
            'Activar' => 'Activo',
            'Solo guardar' => 'Guardado',
        ];

        $validated['estadoActivacion'] = $statusMap[$validated['estadoActivacion']] ?? 'Borrador';

        $validated['user_id'] = Auth::id();

        $chatbot = Chatbots::create($validated);

        return response()->json([
            'message' => 'Chatbot creado exitosamente',
            'chatbot' => $chatbot
        ]);

    }

    public function myAgents()
    {
        $agentes = Chatbots::where('user_id', Auth::id())
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($agentes);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $public_key)
    {
        $chatbot = Chatbots::where('public_key', $public_key)->first();

        if (!$chatbot) {
            return response()->json([
                'message' => 'Agente no encontrado'
            ], 409);
        }

        return response()->json($chatbot);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $public_key)
    {
        $chatbot = Chatbots::where('public_key', $public_key)->firstOrFail();

        if ($request->has('respuestasRapidas') && is_string($request->respuestasRapidas)) {
            $request->merge([
                'respuestasRapidas' => json_decode($request->respuestasRapidas, true)
            ]);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:100',
            'categoria' => 'sometimes|string|in:Ventas,Soporte,Reservas,Educación,Servicios profesionales,Otro',
            'idioma' => 'sometimes|string|in:es,en,fr,de,pt,it',
            'avatar' => 'nullable|sometimes|image|mimes:jpg,jpeg,png,gif|max:2048',
            'estilo' => 'sometimes|string|in:Formal,Casual,Divertido,Profesional,Educativo',
            'nivelTecnico' => 'sometimes|integer|min:0|max:100',
            'usoEmojis' => 'sometimes|string|in:Sí,No,Que los decida según contexto',
            'nombreEmpresa' => 'sometimes|string|max:150',
            'sitioWeb' => 'nullable|sometimes|url|max:255',
            'descripcionEmpresa' => 'sometimes|string|max:1000',
            'horarioAtencion' => 'nullable|sometimes|string|max:200',
            'informacionAdicional' => 'nullable|sometimes|string|max:500',
            'mensajeBienvenida' => 'sometimes|string|max:250',
            'mensajeNoDisponible' => 'sometimes|string|max:200',
            'mensajeAusencia' => 'sometimes|string|max:200',
            'respuestasRapidas' => 'nullable|sometimes|array',
            'respuestasRapidas.*' => 'string|max:1000',
            'color' => 'sometimes|string|regex:/^#[0-9A-F]{6}$/i',
            'posicion' => 'sometimes|string|in:bottom-right,bottom-left,top-right,top-left',
            'mostrarAvatar' => 'sometimes|boolean',
            'sonidoNotificacion' => 'sometimes|boolean',
            'tamanoWidget' => 'nullable|sometimes|string|in:pequeño,mediano,grande',
            'objetivoPrincipal' => 'sometimes|string|in:Generar ventas,Atender clientes,Reservas / agendamiento,Información rápida,FAQ,Todos los anteriores',
            'preguntasFrecuentes' => 'nullable|sometimes|string|max:1000',
            'temasExcluidos' => 'nullable|sometimes|string|max:500',
            'datosCapturar' => 'nullable|sometimes|array',
            'datosCapturar.*' => 'string|in:Nombre,Teléfono,Email,Otro campo',
            'estadoActivacion' => 'sometimes|string|in:Activar,Solo guardar,Guardar como borrador',
        ]);

        if ($request->hasFile('avatar')) {
            if ($chatbot->avatar && \Storage::disk('public')->exists($chatbot->avatar)) {
                \Storage::disk('public')->delete($chatbot->avatar);
            }

            $path = $request->file('avatar')->store('chatbots', 'public');
            $validated['avatar'] = $path;
        } else {
            unset($validated['avatar']);
        }

        $statusMap = [
            'Activar' => 'Activo',
            'Solo guardar' => 'Guardado',
        ];

        $validated['estadoActivacion'] = $statusMap[$validated['estadoActivacion']] ?? 'Borrador';

        $chatbot->update($validated);

        return response()->json([
            'message' => 'Chatbot actualizado correctamente',
            'data' => $chatbot
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $public_key)
    {
        $agente = Chatbots::where('public_key', $public_key)->first();

        if (!$agente) {
            return response()->json(['message' => 'Agente no encontrado'], 404);
        }

        $agente->delete();
        return response()->json(['message' => 'Agente eliminado con éxito']);
    }


    public function delete(string $public_key)
    {
        $agente = Chatbots::where('public_key', $public_key)->first();

        if (!$agente) {
            return response()->json(['message' => 'Agente no encontrado'], 404);
        }

        $agente->update(['status' => 3]);
        return response()->json(['message' => 'Agente eliminado con éxito']);
    }

    public function toggleStatus(string $public_key, Request $request)
    {
        $agente = Chatbots::where('public_key', $public_key)->first();

        $agente->update(['estadoActivacion' => $request->estadoActivacion]);

        return response()->json(['message' => 'Agente editado con éxito', 'activacion', $request->estadoActivacion]);

    }

    

    
}
