<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at ?
                $this->email_verified_at->format('Y-m-d H:i:s') : null,
            'profile' => $this->when($this->relationLoaded('profile'), function () {
                return $this->profile ? [
                    'id' => $this->profile->id,
                    'user_id' => $this->profile->user_id,
                    'profile_image' => $this->profile->profile_image,
                    'profile_image_bg' => $this->profile->profile_image_bg,
                    'phone_number' => $this->profile->phone_number,
                    'age' => $this->profile->age,
                    'bio' => $this->profile->bio,
                    'language' => $this->profile->language,
                    'timezone' => $this->profile->timezone,
                    'is_two_factor_enabled' => $this->profile->is_two_factor_enabled,
                    'require_password_for_changes' => $this->profile->require_password_for_changes,
                    'in_app_notifications' => $this->profile->in_app_notifications,
                    'email_notifications' => $this->profile->email_notifications,
                    'push_notifications' => $this->profile->push_notifications,
                    'sms_notifications' => $this->profile->sms_notifications,
                    'is_active' => $this->profile->is_active,
                    'created_at' => $this->profile->created_at?->format('Y-m-d H:i:s'),
                    'updated_at' => $this->profile->updated_at?->format('Y-m-d H:i:s'),
                ] : null;
            }),
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'guard_name' => $role->guard_name
                    ];
                });
            }),
            'permissions' => $this->whenLoaded('permissions', function () {
                return $this->getAllPermissions()->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'guard_name' => $permission->guard_name
                    ];
                });
            }),
            'chatbots' => $this->whenLoaded('chatbots', function () {
                return $this->chatbots->map(function ($chatbot) {
                    return [
                        'id' => $chatbot->id,
                        'user_id' => $chatbot->user_id,
                        'nombre' => $chatbot->nombre,
                        'nombreEmpresa' => $chatbot->nombreEmpresa,
                        'objetivoPrincipal' => $chatbot->objetivoPrincipal,
                        'avatar' => $chatbot->avatar,
                        'color' => $chatbot->color,
                        'public_key' => $chatbot->public_key,
                        'Status' => $chatbot->estadoActivacion,
                        'created_at' => $chatbot->created_at?->format('Y-m-d H:i:s'),
                        'updated_at' => $chatbot->updated_at?->format('Y-m-d H:i:s'),
                    ];
                });
            }),
            'chatbots_count' => $this->chatbots_count ?? 0,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
