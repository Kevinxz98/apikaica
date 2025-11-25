<?php

namespace App\Policies;

use App\Models\Services;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view services');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create services');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Services $service): bool
    {
        return $user->can('update services');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Services $service): bool
    {
        return $user->can('delete services');
    }

}
