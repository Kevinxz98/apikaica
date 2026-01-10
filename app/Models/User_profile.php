<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User_profile extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'profile_image',
        'profile_image_bg',
        'phone_number',
        'age',
        'bio',
        'language',
        'timezone',
        'is_two_factor_enabled',
        'require_password_for_changes',
        'in_app_notifications',
        'email_notifications',
        'push_notifications',
        'sms_notifications',
        'is_active',
    ];

    protected $casts = [
        'age' => 'integer',
        'is_two_factor_enabled' => 'boolean',
        'require_password_for_changes' => 'boolean',
        'in_app_notifications' => 'boolean',
        'email_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Obtener el usuario asociado al perfil.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
