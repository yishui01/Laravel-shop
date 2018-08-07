<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class SocialInfo extends Model implements JWTSubject
{
    public $fillable = ['avatar', 'nickname', 'gender', 'extra', 'openid', 'unionid', 'type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Rest omitted for brevity

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }


}
