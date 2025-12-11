<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'ormawaID',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
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

    // Relasi ke posts (FK: userID di tabel posts)
    public function posts()
    {
        return $this->hasMany(Post::class, 'userID');
    }

    // Ormawa utama (main ormawa)
    public function mainOrmawa()
    {
        return $this->belongsTo(Ormawa::class, 'ormawaID');
    }

    // Semua ormawa yang diikuti user (via pivot ormawa_user)
    public function ormawas()
    {
        return $this->belongsToMany(
            Ormawa::class,
            'ormawa_user',   // nama tabel pivot
            'user_id',       // FK ke users.id
            'ormawaID'       // FK ke ormawa.id
        );
    }
}
