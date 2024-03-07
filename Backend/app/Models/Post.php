<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';

    protected $fillable = [
        'image',
        'title',
        'image',
        'description',
        'post_type_id',
        'schedule',
        'active',
    ];

    public function post_type(){

        return $this->belongsTo(Post_type::class, 'post_type_id');

    }

    public function hashtags(){

        return $this->belongsToMany(Hashtag::class, 'posts_hashtags', 'hashtag_id', 'post_id');

    }

    public function users(){

        return $this->belongsToMany(User::class, 'users_posts', 'user_id', 'post_id');

    }

}
