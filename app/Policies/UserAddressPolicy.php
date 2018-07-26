<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\UserAddress;

class UserAddressPolicy
{
    use HandlesAuthorization;

    public function update(User $currentUser, UserAddress $userAddress)
    {
        return $currentUser->id === $userAddress->user_id;
    }

}
