<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chatbots;
use Illuminate\Support\Facades\Auth;
use App\Models\chatbot_stats;
use App\Models\chatbot_usage_logs;
use Carbon\Carbon;



class StatisticsChatbotController extends Controller
{
    public function dashboardStats()
    {

        $userId = auth()->id();

        $totalMessages = Chatbots::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->sum('total_messages');

        $totalTokens = Chatbots::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->sum('total_tokens');


        $lastActivity = Chatbots::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->value('last_interaction');

        if ($lastActivity) {
            $carbonDate = Carbon::parse($lastActivity)->locale('es');
            $fechaFormateada = $carbonDate->isoFormat('D MMM, HH:mm');
        } else {
            $fechaFormateada = 'Sin actividad reciente';
        }


        return response()->json([
            'total_messages' => $totalMessages,
            'tokens_used' => $totalTokens,
            'last_activity' => $fechaFormateada,
        ]);

    }

    public function basicStats(string $public_key)
    {
        $userId = auth()->id();

        $stats = Chatbots::where('public_key', $public_key)
            ->where('user_id', $userId)
            ->where('status', 1)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('
                user_id,
                nombre,
                categoria,
                nombreEmpresa,
                sitioWeb,
                avatar,
                color,
                estadoActivacion,
                created_at,
                openai_model,
                status,
                SUM(total_messages) as total_messages,
                SUM(total_tokens) as total_tokens,
                MAX(last_interaction) as last_activity
            ')
            ->groupBy(
                'user_id',
                'nombre',
                'categoria',
                'nombreEmpresa',
                'sitioWeb',
                'avatar',
                'color',
                'estadoActivacion',
                'created_at',
                'openai_model',
                'status')
            ->first();

        // Verificar si el chatbot existe
        if (!$stats || $stats->status != 1) {
            return response()->json(
                ['message' => 'Chatbot no encontrado o eliminado'],
                404
            );
        }

        $fechaFormateada = $stats->last_activity
            ? Carbon::parse($stats->last_activity)
                ->locale('es')
                ->isoFormat('D MMM, HH:mm')
            : 'Sin actividad reciente';
        
        $fechaCreacion = $stats->created_at
            ? Carbon::parse($stats->created_at)
                ->locale('es')
                ->isoFormat('D MMM, HH:mm')
            : 'Sin actividad reciente';

        return response()->json([
            'total_messages' => $stats->total_messages ?? 0,
            'tokens_used' => $stats->total_tokens ?? 0,
            'last_activity' => $fechaFormateada,
            'fechaCreacion' => $fechaCreacion,
            'stats' => $stats,
        ]);

    }

    public function chatbotStats(string $public_key)
    {
        // Buscar el chatbot por la clave pública
        $chatbot = Chatbots::where('public_key', $public_key)->first();

        // Verificar si el chatbot existe
        if (!$chatbot || $chatbot->status != 1) {
            return response()->json(
                ['message' => 'Chatbot no encontrado o eliminado'],
                404
            );
        }

        $user = auth()->id();

        // Verificar permisos del usuario
        if ($chatbot->user_id != $user) {
            return response()->json(
                ['message' => 'Permiso denegado'],
                401
            );
        }

        // Obtener logs de uso para estadísticas adicionales
        $usageLogs = chatbot_usage_logs::forChatbot($chatbot->id)->get();

        // Calcular estadísticas desde los logs
        $totalTokens = $usageLogs->sum('tokens_used');
        $totalMessages = $usageLogs->count();

        // Contar diferentes tipos de eventos
        $eventTypes = $usageLogs->groupBy('event_type')->map(function ($group) {
            return $group->count();
        });

        // Contar dominios fuente únicos
        $uniqueDomains = $usageLogs->whereNotNull('source_domain')->pluck('source_domain')->unique()->count();

        // Obtener últimos logs (últimos 10)
        $recentLogs = $usageLogs->sortByDesc('created_at')->take(10)->values();

        // Estadísticas por tiempo (últimos 30 días)
        $thirtyDaysAgo = now()->subDays(30);
        $recentUsageLogs = chatbot_usage_logs::forChatbot($chatbot->id)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->get();

        $monthlyTokens = $recentUsageLogs->sum('tokens_used');
        $monthlyMessages = $recentUsageLogs->count();

        // Obtener picos de actividad (mensajes por día)
        $activityByDay = $usageLogs->groupBy(function ($log) {
            return $log->created_at->format('Y-m-d');
        })->map(function ($group) {
            return $group->count();
        });

        // Calcular tokens promedio por mensaje
        $avgTokensPerMessage = $totalMessages > 0 ? round($totalTokens / $totalMessages, 2) : 0;

        // Estadísticas de tokens (mínimo, máximo, promedio)
        $tokenStats = [
            'total' => $totalTokens,
            'monthly' => $monthlyTokens,
            'average_per_message' => $avgTokensPerMessage,
            'min_per_message' => $totalMessages > 0 ? $usageLogs->min('tokens_used') : 0,
            'max_per_message' => $totalMessages > 0 ? $usageLogs->max('tokens_used') : 0,
        ];

        // Preparar actividad por día
        $activityOverview = [];
        if ($activityByDay->count() > 0) {
            $sortedActivity = $activityByDay->sortDesc();
            $mostActiveDay = $sortedActivity->first();
            $mostActiveDate = $sortedActivity->keys()->first();

            $activityOverview = [
                'most_active_day' => [
                    'date' => $mostActiveDate,
                    'messages' => $mostActiveDay
                ],
                'total_days_with_activity' => $activityByDay->count(),
                'average_messages_per_day' => round($activityByDay->avg(), 2),
            ];
        } else {
            $activityOverview = [
                'most_active_day' => null,
                'total_days_with_activity' => 0,
                'average_messages_per_day' => 0,
            ];
        }

        // Preparar la respuesta
        $response = [
            'detailed_stats' => [
                'total_messages' => $totalMessages,
                'total_tokens_used' => $totalTokens,
                'monthly_messages' => $monthlyMessages,
                'monthly_tokens_used' => $monthlyTokens,
                'unique_source_domains' => $uniqueDomains,
                'event_type_distribution' => $eventTypes,
            ],
            'token_statistics' => $tokenStats,
            'activity_overview' => $activityOverview,
            'recent_activity' => $recentLogs->map(function ($log) {
                return [
                    'event_type' => $log->event_type,
                    'tokens_used' => $log->tokens_used,
                    'source_domain' => $log->source_domain,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                    'has_input' => !empty($log->input),
                    'has_output' => !empty($log->output),
                ];
            })->values(),
        ];

        // Preparar timeframe
        $timeframe = [];
        if ($usageLogs->count() > 0) {
            $minDate = $usageLogs->min('created_at');
            $maxDate = $usageLogs->max('created_at');

            $timeframe = [
                'total_period' => [
                    'start' => $minDate->format('Y-m-d'),
                    'end' => $maxDate->format('Y-m-d'),
                ],
                'last_30_days' => [
                    'start' => $thirtyDaysAgo->format('Y-m-d'),
                    'end' => now()->format('Y-m-d'),
                ]
            ];
        } else {
            $timeframe = [
                'total_period' => null,
                'last_30_days' => [
                    'start' => $thirtyDaysAgo->format('Y-m-d'),
                    'end' => now()->format('Y-m-d'),
                ]
            ];
        }

        return response()->json([
            'message' => 'Estadísticas obtenidas exitosamente',
            'statistics' => $response,
            'timeframe' => $timeframe
        ]);
    }

    public function getSevenDaysStats(string $public_key)
    {
        // Buscar el chatbot por la clave pública
        $chatbot = Chatbots::where('public_key', $public_key)->first();

        // Verificar si el chatbot existe
        if (!$chatbot || $chatbot->status != 1) {
            return response()->json(
                ['message' => 'Chatbot no encontrado o eliminado'],
                404
            );
        }

        $user = auth()->id();

        // Verificar permisos del usuario
        if ($chatbot->user_id != $user) {
            return response()->json(
                ['message' => 'Permiso denegado'],
                401
            );
        }

        // Definir el periodo de 7 días
        $endDate = Carbon::now()->endOfDay();
        $startDate = Carbon::now()->subDays(6)->startOfDay(); // 7 días incluyendo hoy

        // Consulta principal para obtener estadísticas por día
        $statsByDay = chatbot_usage_logs::forChatbot($chatbot->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
            DATE(created_at) as date,
            COUNT(*) as interactions,
            SUM(tokens_used) as tokens_used,
            COUNT(DISTINCT DATE(created_at), source_domain) as unique_domains_per_day
        ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Crear un array con todos los días del periodo (incluso si no hay datos)
        $allDates = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $allDates[$date] = [
                'date' => $date,
                'interactions' => 0,
                'tokens_used' => 0,
                'unique_domains' => 0
            ];
        }

        // Combinar datos reales con todos los días
        foreach ($statsByDay as $stat) {
            $allDates[$stat->date] = [
                'date' => $stat->date,
                'interactions' => (int) $stat->interactions,
                'tokens_used' => (int) $stat->tokens_used,
                'unique_domains' => (int) $stat->unique_domains_per_day
            ];
        }

        // Obtener totales del periodo
        $totalStats = chatbot_usage_logs::forChatbot($chatbot->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
            COUNT(*) as total_interactions,
            SUM(tokens_used) as total_tokens,
            COUNT(DISTINCT source_domain) as total_unique_domains,
            AVG(tokens_used) as avg_tokens_per_interaction
        ')
            ->first();

        // Obtener datos de tendencia (comparar con la semana anterior si aplica)
        $previousWeekStart = Carbon::now()->subDays(13)->startOfDay();
        $previousWeekEnd = Carbon::now()->subDays(7)->endOfDay();

        $previousWeekStats = chatbot_usage_logs::forChatbot($chatbot->id)
            ->whereBetween('created_at', [$previousWeekStart, $previousWeekEnd])
            ->selectRaw('
            COUNT(*) as interactions,
            SUM(tokens_used) as tokens_used
        ')
            ->first();

        // Calcular porcentajes de cambio
        $interactionChange = 0;
        $tokensChange = 0;

        if ($previousWeekStats && $previousWeekStats->interactions > 0) {
            $interactionChange = (($totalStats->total_interactions - $previousWeekStats->interactions) / $previousWeekStats->interactions) * 100;
            $tokensChange = (($totalStats->total_tokens - $previousWeekStats->tokens_used) / $previousWeekStats->tokens_used) * 100;
        }

        // Preparar la respuesta
        $response = [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'days_count' => 7
            ],
            'daily_stats' => array_values($allDates),
            'summary' => [
                'total_interactions' => (int) $totalStats->total_interactions,
                'total_tokens_used' => (int) $totalStats->total_tokens,
                'total_unique_domains' => (int) $totalStats->total_unique_domains,
                'average_tokens_per_interaction' => $totalStats->avg_tokens_per_interaction ?
                    round($totalStats->avg_tokens_per_interaction, 2) : 0,
                'average_interactions_per_day' => round($totalStats->total_interactions / 7, 2),
                'average_tokens_per_day' => round($totalStats->total_tokens / 7, 2)
            ],
            'trends' => [
                'interaction_change_percentage' => round($interactionChange, 2),
                'tokens_change_percentage' => round($tokensChange, 2),
                'previous_week_comparison' => $previousWeekStats ? [
                    'interactions' => (int) $previousWeekStats->interactions,
                    'tokens_used' => (int) $previousWeekStats->tokens_used,
                    'period' => [
                        'start' => $previousWeekStart->format('Y-m-d'),
                        'end' => $previousWeekEnd->format('Y-m-d')
                    ]
                ] : null
            ],
            'analysis' => [
                'most_active_day' => $statsByDay->isNotEmpty() ?
                    $statsByDay->sortByDesc('interactions')->first() : null,
                'least_active_day' => $statsByDay->isNotEmpty() ?
                    $statsByDay->sortBy('interactions')->first() : null,
                'peak_tokens_day' => $statsByDay->isNotEmpty() ?
                    $statsByDay->sortByDesc('tokens_used')->first() : null
            ]
        ];

        return response()->json([
            'message' => 'Estadísticas de 7 días obtenidas exitosamente',
            'chatbot_info' => [
                'id' => $chatbot->id,
                'name' => $chatbot->name,
                'public_key' => $chatbot->public_key
            ],
            'data' => $response
        ]);
    }

}


