<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ormawa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ormawa';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',       // PIC/creator (optional)
        'name',
        'photo_path',
        'type_ormawa',
        'category_ormawa',
        'status_oprec',
        'description',
    ];

    public function getRouteKeyName()
    {
        return 'id';
    }

    // PIC/creator: ormawa.user_id -> users.id
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // members via pivot: ormawa_user.ormawaID -> ormawa.id
    //                    ormawa_user.user_id  -> users.id
    public function members()
    {
        return $this->belongsToMany(
            User::class,
            'ormawa_user',
            'ormawaID',
            'user_id'
        );
    }

    // posts: posts.ormawaID -> ormawa.id
    public function posts()
    {
        return $this->hasMany(Post::class, 'ormawaID', 'id');
    }

    // users yang menjadikan ormawa ini sebagai mainOrmawa: users.ormawaID -> ormawa.id
    public function primaryUsers()
    {
        return $this->hasMany(User::class, 'ormawaID', 'id');
    }
}
