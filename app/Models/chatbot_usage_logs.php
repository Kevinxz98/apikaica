<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
class chatbot_usage_logs extends Model
{
    use HasFactory;

    protected $fillable = [
        'chatbot_id',
        'session_id',     
        'ip_address',     
        'user_agent',     
        'metadata',       
        'event_type',
        'input',
        'output',
        'tokens_used',
        'source_domain',
    ];

     protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function chatbot()
    {
        return $this->belongsTo(Chatbots::class);
    }

    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeForChatbot($query, $chatbotId)
    {
        return $query->where('chatbot_id', $chatbotId);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeWithConversations($query, $chatbotId = null)
    {
        $query = $query->select([
                'session_id',
                DB::raw('MIN(created_at) as conversation_start'),
                DB::raw('MAX(created_at) as conversation_end'),
                DB::raw('COUNT(*) as message_count'),
                DB::raw('MAX(source_domain) as source'),
                DB::raw('MAX(ip_address) as ip'),
                DB::raw('GROUP_CONCAT(CONCAT("[Usuario]: ", input) ORDER BY created_at SEPARATOR "\n") as user_messages'),
                DB::raw('GROUP_CONCAT(CONCAT("[Chatbot]: ", output) ORDER BY created_at SEPARATOR "\n") as bot_messages')
            ])
            ->groupBy('session_id')
            ->orderBy('conversation_end', 'DESC');

        if ($chatbotId) {
            $query->where('chatbot_id', $chatbotId);
        }

        return $query;
    }
}
