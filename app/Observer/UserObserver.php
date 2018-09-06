<?php

namespace App\Observer;

use App\Models\User;

class UserObserver
{

    public function saving(User $user)
    {
        if (!$user->avatar) {
            //如果用户头像为空，随机设置一个头像
            $user->avatar = get_rand_imgurl();
        }

    }

    public function deleted(User $user)
    {

    }

    public function getRandAvatar()
    {
        $avatars = [];
    }

}