<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User_profile;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        $user->load('profile');

        if (!$user->profile) {
            $user->profile()->create([
                'language' => 'es',
                'timezone' => config('app.timezone', 'UTC'),
                'in_app_notifications' => true,
                'email_notifications' => true,
                'sms_notifications' => true,
                'is_active' => true,
            ]);

            $user->load('profile');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                ],
                'profile' => $user->profile,
            ],
            'message' => 'Perfil obtenido correctamente'
        ]);


    }

    public function getUsers()
    {
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'profile_image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'phone_number' => 'sometimes|nullable|string|max:20',
            'age' => 'sometimes|nullable|integer|min:1|max:120',
            'bio' => 'sometimes|nullable|string|max:500',
            'language' => 'sometimes|nullable|string|max:10',
            'timezone' => 'sometimes|nullable|string|max:50',
        ]);

        if (array_key_exists('name', $validated)) {
            $user->update([
                'name' => $validated['name'],
            ]);
        }

        $profileData = collect($validated)->except('name')->toArray();


        if ($request->hasFile('profile_image')) {

            if ($user->profile?->profile_image) {
                Storage::disk('public')->delete($user->profile->profile_image);
            }

            $profileData['profile_image'] = $request
                ->file('profile_image')
                ->store('profiles', 'public');
        }

        if (!empty($profileData)) {
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileData
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado correctamente',
            'user' => $user->load('profile'),
        ], 200);

    }

    public function updatePass(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Contraseña actualizada correctamente',
        ]);

    }

    public function updateNotifications(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'in_app_notifications' => 'sometimes|boolean',
            'email_notifications' => 'sometimes|boolean',
            'push_notifications' => 'sometimes|boolean',
            'sms_notifications' => 'sometimes|boolean',
        ]);

        if (!empty($validated)) {
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                $validated
            );
        }

        return response()->json([
            'message' => 'Preferencias de notificaciones actualizadas correctamente',
            'profile' => $user->profile()->first(),
        ]);

    }

    public function deleteProfileImage()
    {
        $user = auth()->user();
        $profile = $user->profile;

        if (!$profile || !$profile->profile_image) {
            return response()->json([
                'message' => 'No hay imagen para eliminar'
            ], 404);
        }

        if (Storage::disk('public')->exists($profile->profile_image)) {
            Storage::disk('public')->delete($profile->profile_image);
        }

        $profile->update([
            'profile_image' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Imagen de perfil eliminada correctamente'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
