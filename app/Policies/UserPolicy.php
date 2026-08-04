<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    // BAGONG METHOD: Titingnan nito ang user bago ang anumang Policy check.
    public function before(User $user, string $ability)
    {
        // Kung ang user ay SUPER_ADMIN, ibalik ang TRUE kaagad.
        if ($user->role === Role::SUPER_ADMIN->value) {
            return true;
        }

        // Kung hindi SUPER_ADMIN, ituloy ang Policy method (e.g., delete)
        return null;
    }

    // Ang delete method ay tatawagin lang kung HINDI SUPER_ADMIN ang user.
    public function delete(User $user, User $model)
    {
        // Dito mo ilalagay ang logic para sa ibang users (e.g., ADMIN)
        // return $user->role === Role::ADMIN->value;
        
        // Pero kung gusto mo lang na Super Admin ang may delete, tanggalin mo na lang ang laman nito o ibalik ang null/false.
        return false; 
    }
}