<?php

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        return [
            'name'          => $user->name,
            'email'         => $user->email,
            'avatar'        => $user->avatar,
            'phone'         => $user->phone,
      ];
    }

}

