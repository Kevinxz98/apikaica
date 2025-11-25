<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotInstance extends Model
{
     protected $fillable = [
        'user_id',
        'service_id',
        'client_slug',
        'name',
        'welcome_message',
        'color',
        'avatar',
        'knowledge_mode',
        'knowledge_manual',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Services::class);
    }
}
