<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class chatbot_stats extends Model
{
    use HasFactory;

    protected $fillable = [
        'chatbot_id',
        'messages_count',
        'conversations_count',
        'users_count',
        'tokens_used_month',
        'last_activity',
    ];

    protected $casts = [
        'messages_count' => 'integer',
        'conversations_count' => 'integer',
        'users_count' => 'integer',
        'tokens_used_month' => 'integer',
        'last_activity' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function chatbot()
    {
        return $this->belongsTo(Chatbots::class);
    }

    public function updateFromLog(chatbot_usage_logs $log)
    {
        $this->messages_count++;
        $this->tokens_used_month += $log->tokens_used;
        $this->last_activity = now();
        
        // You might want to add logic to track unique users and conversations
        // This would require additional tracking in the logs
        
        $this->save();
    }

    public function resetMonthlyTokens()
    {
        $this->tokens_used_month = 0;
        $this->save();
    }

    public function incrementConversations()
    {
        $this->conversations_count++;
        $this->save();
    }

    public function incrementUsers()
    {
        $this->users_count++;
        $this->save();
    }

    public function getAverageTokensPerMessageAttribute()
    {
        if ($this->messages_count === 0) {
            return 0;
        }
        
        return round($this->tokens_used_month / $this->messages_count, 2);
    }

    public function scopeForChatbot($query, $chatbotId)
    {
        return $query->where('chatbot_id', $chatbotId);
    }
}
