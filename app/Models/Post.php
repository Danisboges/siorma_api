<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';
    protected $primaryKey = 'postID';

    // Jika PK bukan auto-increment integer, sesuaikan:
    // public $incrementing = true;
    // protected $keyType = 'int';

    protected $fillable = [
        'ormawaID',
        'userID',
        'title',
        'description',
        'posterPath',
        'status',
    ];

    public function ormawa()
    {
        return $this->belongsTo(Ormawa::class, 'ormawaID');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userID');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'post_id', 'postID');
    }
}
