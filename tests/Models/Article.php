<?php

namespace ChristianKuri\LaravelFavorite\Test\Models;

use Illuminate\Database\Eloquent\Model;
use ChristianKuri\LaravelFavorite\Traits\Favoriteable;

class Article extends Model
{
    use Favoriteable;

    protected $table = 'articles';
    protected $guarded = [];
}
