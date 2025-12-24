<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';
    protected $primaryKey = 'postID';

    // jika postID auto increment int:
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'ormawaID',
        'userID',
        'title',
        'description',
        'posterPath',
        'status',
    ];

    // posts.ormawaID -> ormawa.id
    public function ormawa()
    {
        return $this->belongsTo(Ormawa::class, 'ormawaID', 'id');
    }

    // posts.userID -> users.id
    public function user()
    {
        return $this->belongsTo(User::class, 'userID', 'id');
    }

    // registrations.post_id -> posts.postID (sesuai model Anda)
    public function registrations()
    {
        return $this->hasMany(Registration::class, 'post_id', 'postID');
    }
}
