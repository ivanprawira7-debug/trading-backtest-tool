<?php

namespace App\Policies;

use App\Models\BacktestSession;
use App\Models\User;

class BacktestSessionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BacktestSession $backtestSession): bool
    {
        return $user->id === $backtestSession->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BacktestSession $backtestSession): bool
    {
        return $user->id === $backtestSession->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BacktestSession $backtestSession): bool
    {
        return $user->id === $backtestSession->user_id;
    }
}
