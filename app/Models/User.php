<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->activation_token = Str::random(10);
        });
    }
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $table = 'users';



    public function gravatar($size = '100')
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://secure.gravatar.com/avatar/$hash?s=$size";//cn.gravatar.com
    }


    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    public function feed()
    {
        $user_ids = $this->followings->pluck('id')->toArray();
        array_push($user_ids, $this->id);
        return Status::whereIn('user_id', $user_ids)
                              ->with('user')
                              ->orderBy('created_at', 'desc');
    }
    //获取粉丝关系列表
    public function followers()
    {
        return $this->belongsToMany(User::Class, 'followers', 'user_id', 'follower_id');
    }
    //关注人列表
    public function followings()
    {
        return $this->belongsToMany(User::Class, 'followers', 'follower_id', 'user_id');
    }
    //关注操作
    public function follow($user_ids)
    {
        if ( ! is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->sync($user_ids, false);
    }
    //取消关注操作
    public function unfollow($user_ids)
    {
        if ( ! is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->detach($user_ids);
    }
    //判断当前登录的用户 A 是否关注了用户 B
    public function isFollowing($user_id)
    {
        return $this->followings->contains($user_id);
    }
}
