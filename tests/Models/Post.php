<?php

namespace ChristianKuri\LaravelFavorite\Test\Models;

use Illuminate\Database\Eloquent\Model;
use ChristianKuri\LaravelFavorite\Traits\Favoriteable;

class Post extends Model
{
    use Favoriteable;

    protected $table = 'posts';
    protected $guarded = [];
}
