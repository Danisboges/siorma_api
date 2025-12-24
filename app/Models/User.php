<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'ormawaID', // FK -> ormawa.id (primary/main ormawa)
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // posts: posts.userID -> users.id
    public function posts()
    {
        return $this->hasMany(Post::class, 'userID', 'id');
    }

    // main ormawa: users.ormawaID -> ormawa.id
    public function mainOrmawa()
    {
        return $this->belongsTo(Ormawa::class, 'ormawaID', 'id');
    }

    // membership ormawa via pivot: ormawa_user.user_id -> users.id
    //                              ormawa_user.ormawaID -> ormawa.id
    public function ormawas()
    {
        return $this->belongsToMany(
            Ormawa::class,
            'ormawa_user',
            'user_id',
            'ormawaID'
        );
    }

    // kalau Anda ingin relasi "ormawa yang dibuat/dipimpin oleh user" (ormawa.user_id)
    public function createdOrmawas()
    {
        return $this->hasMany(Ormawa::class, 'user_id', 'id');
    }
}
