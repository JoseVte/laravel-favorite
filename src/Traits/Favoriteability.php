<?php

namespace ChristianKuri\LaravelFavorite\Traits;

use ChristianKuri\LaravelFavorite\Models\Favorite;

/**
 * This file is part of Laravel Favorite,.
 *
 * @license MIT
 */
trait Favoriteability
{
    /**
     * Define a one-to-many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id');
    }

    /**
     * Return a collection with the User favorited Model.
     * The Model needs to have the Favoriteable trait.
     *
     * @param $class *** Accepts for example: Post::class or 'App\Post' ****
     *
     * @return \Illuminate\Support\Collection
     */
    public function favorite($class)
    {
        if ($this->relationLoaded('favorites')) {
            /** @var \Illuminate\Database\Eloquent\Collection $favoritesCollection */
            $favoritesCollection = $this->favorites->where('favoriteable_type', $class)->load('favoriteable');
        } else {
            $favoritesCollection = $this->favorites()->where('favoriteable_type', $class)->with('favoriteable')->get();
        }

        return $favoritesCollection->mapWithKeys(function ($item) {
            if (isset($item['favoriteable'])) {
                return [$item['favoriteable']->id => $item['favoriteable']];
            }

            return [];
        });
    }

    /**
     * Add the object to the User favorites.
     * The Model needs to have the Favoriteable trai.
     *
     * @param object $object
     */
    public function addFavorite($object)
    {
        $object->addFavorite($this->id);
    }

    /**
     * Remove the Object from the user favorites.
     * The Model needs to have the Favoriteable trai.
     *
     * @param object $object
     */
    public function removeFavorite($object)
    {
        $object->removeFavorite($this->id);
    }

    /**
     * Toggle the favorite status from this Object from the user favorites.
     * The Model needs to have the Favoriteable trai.
     *
     * @param object $object
     */
    public function toggleFavorite($object)
    {
        $object->toggleFavorite($this->id);
    }

    /**
     * Check if the user has favorited this Object
     * The Model needs to have the Favoriteable trai.
     *
     * @param object $object
     *
     * @return bool
     */
    public function isFavorited($object)
    {
        return $object->isFavorited($this->id);
    }

    /**
     * Check if the user has favorited this Object
     * The Model needs to have the Favoriteable trai.
     *
     * @param object $object
     *
     * @return bool
     */
    public function hasFavorited($object)
    {
        return $object->isFavorited($this->id);
    }
}
