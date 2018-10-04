<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Events\Registered; //用户注册完会触发这个事件，这事件是laravel内置的
use App\Notifications\EmailVerificationNotification; //通知类

class RegisteredListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        //获取用户
        $user = $event->user;
        //发送邮件
        if ($user->email) {
            $user->notify(new EmailVerificationNotification());
        }
    }
}
