<?php

namespace App\Http\Controllers;

use App\Models\chatbot_usage_logs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Chatbots;

class ChatController extends Controller
{
    /**
     * Obtener todas las conversaciones de los chatbots del usuario
     */
    public function getConversations(Request $request, $chatbotId = null)
    {
        try {
            $chatbotId = $request->input('chatbot_id', $chatbotId);

            if (!$chatbotId) {
                return response()->json([
                    'success' => false,
                    'message' => 'chatbot_id es requerido'
                ], 400);
            }

            $chatbot = Chatbots::find($chatbotId);


            if (!$chatbot) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chatbot no encontrado'
                ], 404);
            }

            $user = auth()->user();

            if (
                !$user->hasRole('admin') &&
                $chatbot->user_id !== $user->id
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver este historial'
                ], 403);
            }


            $conversations = chatbot_usage_logs::withConversations($chatbotId)
                ->orderBy('conversation_end', 'DESC')
                ->get()
                ->map(function ($conversation) use ($chatbot) {
                    return [
                        'session_id' => $conversation->session_id,
                        'title' => $this->cleanUtf8(
                            $this->generateConversationTitle($conversation->user_messages)
                        ),
                        'last_message' => $this->cleanUtf8(
                            $this->getLastMessage($conversation->bot_messages)
                        ),
                        'last_message_time' => $conversation->conversation_end,
                        'message_count' => $conversation->message_count,
                        'created_at' => $conversation->conversation_start,
                        'source' => $conversation->source,
                        'ip' => $conversation->ip,
                        'name' => 'Chat Session',
                        'image' => $chatbot->avatar
                            ? asset('storage/' . $chatbot->avatar)
                            : asset('assets/images/brand-logos/chatbot.png'),
                        'status' => 'online',
                        'chatMsgUnread' => false,
                        'time' => \Carbon\Carbon::parse($conversation->conversation_end)->format('h:i A'),
                    ];
                });


            return response()->json([
                'success' => true,
                'chatbot_id' => (int) $chatbotId,
                'chatbot' => [
                    'id' => $chatbot->id,
                    'name' => $chatbot->name,
                    'color' => $chatbot->color,
                    'avatar' => $chatbot->avatar ? asset('storage/' . $chatbot->avatar) : null,
                    'description' => $chatbot->description,
                ],
                'conversations' => $conversations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTrace()
            ], 500);
        }
    }

    private function cleanUtf8($value)
    {
        return is_string($value)
            ? mb_convert_encoding($value, 'UTF-8', 'UTF-8')
            : $value;
    }

    /**
     * Obtener mensajes de una sesión específica
     */
    public function getSessionMessages(Request $request, $sessionId)
    {
        try {
            $chatbotId = $request->input('chatbot_id');

            if (!$chatbotId) {
                return response()->json([
                    'success' => false,
                    'message' => 'chatbot_id es requerido'
                ], 400);
            }

            $chatbot = Chatbots::find($chatbotId);

            $user = auth()->user();

            if (
                !$user->hasRole('admin') &&
                $chatbot->user_id !== $user->id
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver este historial'
                ], 403);
            }

            $messages = chatbot_usage_logs::where('session_id', $sessionId)
                ->where('chatbot_id', $chatbotId)
                ->where('output', '!=', null)
                ->orderBy('created_at', 'ASC')
                ->get(['input', 'output', 'created_at', 'event_type'])
                ->flatMap(function ($log) {
                    $messages = [];

                    // Mensaje del usuario
                    if ($log->input) {
                        $messages[] = [
                            'content' => $log->input,
                            'sender' => 'user',
                            'time' => $log->created_at->format('h:i A'),
                            'date' => $log->created_at->format('Y-m-d'),
                            'type' => 'text'
                        ];
                    }

                    // Respuesta del chatbot
                    if ($log->output) {
                        $messages[] = [
                            'content' => $log->output,
                            'sender' => 'bot',
                            'time' => $log->created_at->format('h:i A'),
                            'date' => $log->created_at->format('Y-m-d'),
                            'type' => 'text'
                        ];
                    }

                    return $messages;
                })
                ->values();

            return response()->json([
                'success' => true,
                'session_id' => $sessionId,
                'chatbot_id' => (int) $chatbotId,
                'chatbot' => $chatbot ? [
                    'id' => $chatbot->id,
                    'name' => $chatbot->name,
                    'avatar' => $chatbot->avatar ? asset('storage/' . $chatbot->avatar) : null,
                ] : null,
                'messages' => $messages
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener mensajes'
            ], 500);
        }
    }

    /**
     * Generar un título para la conversación
     */
    private function generateConversationTitle($userMessages)
    {
        if (!$userMessages)
            return 'Nueva conversación';

        $firstMessage = explode("\n", $userMessages)[0] ?? '';
        $cleanMessage = str_replace('[Usuario]: ', '', $firstMessage);

        return strlen($cleanMessage) > 30
            ? substr($cleanMessage, 0, 30) . '...'
            : $cleanMessage;
    }

    /**
     * Obtener el último mensaje
     */
    private function getLastMessage($botMessages)
    {
        if (!$botMessages)
            return 'Sin mensajes';

        $messages = explode("\n", $botMessages);
        $lastMessage = end($messages) ?? '';
        $cleanMessage = str_replace('[Chatbot]: ', '', $lastMessage);

        return strlen($cleanMessage) > 40
            ? substr($cleanMessage, 0, 40) . '...'
            : $cleanMessage;
    }
}