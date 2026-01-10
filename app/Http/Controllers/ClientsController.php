<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Http\Resources\UserResource;

class ClientsController extends Controller
{
    public function index(Request $request)
    {
        // Verificar si el usuario autenticado es admin
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado. Solo administradores pueden ver todos los usuarios.'
            ], 403);
        }

        // Validar parámetros de consulta
        $validator = Validator::make($request->all(), [
            'per_page' => 'integer',
            'search' => 'nullable|string|max:255',
            'sort_by' => 'nullable|in:name,email,created_at',
            'sort_order' => 'nullable|in:asc,desc',
            'with' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Configurar relaciones a cargar
        $withRelations = ['profile', 'roles', 'permissions', 'chatbots'];
        if (isset($validated['with'])) {
            if ($validated['with'] === 'all') {
                $withRelations = ['profile', 'roles', 'permissions', 'chatbots'];
            } else {
                $withRelations = explode(',', $validated['with']);
            }
        }

        // Construir query - SIEMPRE traer todo
        $query = User::with($withRelations)
            ->withCount('chatbots');

        // Si hay búsqueda, aplicarla (opcional, pero mejor hacerlo en frontend)
        // Removemos la búsqueda en backend ya que lo hacemos en frontend

        // Si hay ordenamiento, aplicarlo
        if (isset($validated['sort_by'])) {
            $sortOrder = $validated['sort_order'] ?? 'desc';
            $query->orderBy($validated['sort_by'], $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // SIEMPRE traer todos los usuarios
        $users = $query->get();

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users),
            'meta' => [
                'total' => $users->count(),
                'fetched_all' => true,
                'timestamp' => now()->toDateTimeString()
            ]
        ]);
    }

    public function statistics()
    {
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado.'
            ], 403);
        }

        $totalUsers = User::count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $admins = User::role('admin')->count();
        $todayUsers = User::whereDate('created_at', today())->count();
        $weekUsers = User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $monthUsers = User::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $totalUsers,
                'verified' => $verifiedUsers,
                'admins' => $admins,
                'today' => $todayUsers,
                'this_week' => $weekUsers,
                'this_month' => $monthUsers,
            ]
        ]);
    }

    public function show($id)
    {
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado.'
            ], 403);
        }

        $client = User::with(['profile', 'roles', 'permissions', 'chatbots'])
        ->withCount('chatbots')
        ->find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new UserResource($client)
        ]);

    }

}
