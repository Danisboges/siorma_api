<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
        'full_name',
        'nim',
        'email',
        'phone',
        'organization',
        'reason',
        'status',
        'cv_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        // posts.pk = postID
        return $this->belongsTo(Post::class, 'post_id', 'postID');
    }
}
