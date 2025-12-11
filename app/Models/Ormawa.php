<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ormawa extends Model
{
    use HasFactory, SoftDeletes;

    // Tabelnya bernama 'ormawa'
    protected $table = 'ormawa';

    protected $fillable = [
        'user_id',          // creator / penanggung jawab utama
        'name',
        'photo_path',
        'type_ormawa',
        'category_ormawa',
        'status_oprec',
        'description',
    ];

    // User creator (yang buat data ormawa ini)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Semua user yang terhubung ke ormawa ini (via pivot ormawa_user)
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'ormawa_user',   // nama tabel pivot
            'ormawaID',      // FK di pivot -> ormawa.id
            'user_id'        // FK di pivot -> users.id
        );
    }

    // Post yang dimiliki ormawa ini
    public function posts()
    {
        return $this->hasMany(Post::class, 'ormawaID');
    }
}
