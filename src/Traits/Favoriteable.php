<?php

namespace ChristianKuri\LaravelFavorite\Traits;

use Illuminate\Support\Facades\Auth;
use ChristianKuri\LaravelFavorite\Models\Favorite;

/**
 * This file is part of Laravel Favorite,.
 *
 * @license MIT
 *
 * @property \Illuminate\Database\Eloquent\Collection favorites
 * @property int favoritesCount
 */
trait Favoriteable
{
    /**
     * Define a polymorphic one-to-many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }

    /**
     * Add this Object to the user favorites.
     *
     * @param int $user_id [if  null it's added to the auth user]
     */
    public function addFavorite($user_id = null)
    {
        $favorite = new Favorite(['user_id' => ($user_id) ? $user_id : Auth::id()]);
        $this->favorites()->save($favorite);
    }

    /**
     * Remove this Object from the user favorites.
     *
     * @param int $user_id [if  null it's added to the auth user]
     */
    public function removeFavorite($user_id = null)
    {
        $this->favorites()->where('user_id', ($user_id) ? $user_id : Auth::id())->delete();
    }

    /**
     * Toggle the favorite status from this Object.
     *
     * @param int $user_id [if  null its added to the auth user]
     */
    public function toggleFavorite($user_id = null)
    {
        $this->isFavorited($user_id) ? $this->removeFavorite($user_id) : $this->addFavorite($user_id);
    }

    /**
     * Check if the user has favorited this Object.
     *
     * @param int $user_id [if  null it's added to the auth user]
     *
     * @return bool
     */
    public function isFavorited($user_id = null)
    {
        $user_id = ($user_id) ? $user_id : Auth::id();
        if ($this->relationLoaded('favorites')) {
            return $this->favorites->contains('user_id', $user_id);
        }

        return $this->favorites()->where('user_id', $user_id)->exists();
    }

    /**
     * Return a collection with the Users who marked as favorite this Object.
     *
     * @return \Illuminate\Support\Collection
     */
    public function favoritedBy()
    {
        if ($this->relationLoaded('favorites')) {
            $favoritesCollection = $this->favorites->load('user');
        } else {
            $favoritesCollection = $this->favorites()->with('user')->get();
        }

        return $favoritesCollection->mapWithKeys(function ($item) {
            return [$item['user']->id => $item['user']];
        });
    }

    /**
     * Count the number of favorites.
     *
     * @return int
     */
    public function getFavoritesCountAttribute()
    {
        if ($this->relationLoaded('favorites')) {
            return $this->favorites->count();
        }

        return $this->favorites()->count();
    }

    /**
     * Count the number of favorites.
     *
     * @return int
     */
    public function favoritesCount()
    {
        return $this->favoritesCount;
    }

    /**
     * Add deleted observer to delete favorites registers.
     */
    public static function bootFavoriteable()
    {
        static::deleted(
            function ($model) {
                $model->favorites()->delete();
            }
        );
    }
}
