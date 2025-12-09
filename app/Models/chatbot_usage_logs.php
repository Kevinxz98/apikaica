<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class chatbot_usage_logs extends Model
{
    use HasFactory;

    protected $fillable = [
        'chatbot_id',
        'event_type',
        'input',
        'output',
        'tokens_used',
        'source_domain',
    ];

     protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function chatbot()
    {
        return $this->belongsTo(Chatbots::class);
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
}
