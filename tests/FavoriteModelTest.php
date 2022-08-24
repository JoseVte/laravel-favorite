<?php

namespace ChristianKuri\LaravelFavorite\Test;

use ChristianKuri\LaravelFavorite\Test\Models\Post;
use ChristianKuri\LaravelFavorite\Test\Models\User;
use ChristianKuri\LaravelFavorite\Test\Models\Article;

class FavoriteModelTest extends TestCase
{
    /** @test */
    public function modelsCanAddToFavoritesWithAuthUser()
    {
        $article = Article::first();
        $user = User::first();
        $this->be($user);

        $article->addFavorite();

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'favoriteable_id' => $article->id,
            'favoriteable_type' => get_class($article),
        ]);

        $this->assertTrue($article->isFavorited());
    }

    /** @test */
    public function modelsCanRemoveFromFavoritesWithAuthUser()
    {
        $article = Article::first();
        $user = User::first();
        $this->be($user);

        $article->removeFavorite();

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'favoriteable_id' => $article->id,
            'favoriteable_type' => get_class($article),
        ]);

        $this->assertFalse($article->isFavorited());
    }

    /** @test */
    public function modelsCanToggleTheirFavoriteStatusWithAuthUser()
    {
        $article = Article::first();
        $user = User::first();
        $this->be($user);

        $article->toggleFavorite();

        $this->assertTrue($article->isFavorited());

        $article->toggleFavorite();

        $this->assertFalse($article->isFavorited());
    }

    /** @test */
    public function modelsCanAddToFavoritesWithoutTheAuthUser()
    {
        $post = Post::first();
        $post->addFavorite(2);

        $this->assertDatabaseHas('favorites', [
            'user_id' => 2,
            'favoriteable_id' => $post->id,
            'favoriteable_type' => get_class($post),
        ]);

        $this->assertTrue($post->isFavorited(2));
    }

    /** @test */
    public function modelsCanRemoveFromFavoritesWithoutTheAuthUser()
    {
        $post = Post::first();
        $post->removeFavorite(2);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => 2,
            'favoriteable_id' => $post->id,
            'favoriteable_type' => get_class($post),
        ]);

        $this->assertFalse($post->isFavorited(2));
    }

    /** @test */
    public function modelsCanToggleTheirFavoriteStatusWithoutTheAuthUser()
    {
        $post = Post::first();
        $post->toggleFavorite(2);

        $this->assertTrue($post->isFavorited(2));

        $post->toggleFavorite(2);

        $this->assertFalse($post->isFavorited(2));
    }

    /** @test */
    public function userModelCanAddToFavoritesOtherModels()
    {
        $user = User::first();
        $article = Article::first();

        $user->addFavorite($article);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'favoriteable_id' => $article->id,
            'favoriteable_type' => get_class($article),
        ]);

        $this->assertTrue($user->hasFavorited($article));
    }

    /** @test */
    public function userModelCanRemoveFromFavoritesAnotherModels()
    {
        $user = User::first();
        $article = Article::first();

        $user->removeFavorite($article);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'favoriteable_id' => $article->id,
            'favoriteable_type' => get_class($article),
        ]);

        $this->assertFalse($user->isFavorited($article));
    }

    /** @test */
    public function userModelCanToggleHisFavoriteModels()
    {
        $user = User::first();
        $article = Article::first();

        $user->toggleFavorite($article);

        $this->assertTrue($user->hasFavorited($article));

        $user->toggleFavorite($article);

        $this->assertFalse($user->isFavorited($article));
    }

    /** @test */
    public function aUserCanReturnHisFavoritedModels()
    {
        $user = User::first();

        $article1 = Article::find(1);
        $article2 = Article::find(2);
        $article3 = Article::find(3);

        $post1 = Post::find(1);
        $post2 = Post::find(2);

        $user->addFavorite($article1);
        $user->addFavorite($article2);
        $user->addFavorite($article3);

        $user->addFavorite($post1);
        $user->addFavorite($post2);

        $this->assertEquals(3, $user->favorite(Article::class)->count());
        $this->assertEquals(2, $user->favorite(Post::class)->count());

        $user->removeFavorite($article1);
        $user->removeFavorite($article2);
        $user->removeFavorite($article3);

        $user->removeFavorite($post1);
        $user->removeFavorite($post2);

        $this->assertEquals(0, $user->favorite(Article::class)->count());
        $this->assertEquals(0, $user->favorite(Post::class)->count());
    }

    /** @test */
    public function aModelKnowsHowManyUsersHaveFavoritedHim()
    {
        $article = Article::first();

        $article->toggleFavorite(1);
        $article->toggleFavorite(2);
        $article->toggleFavorite(3);

        $this->assertEquals(3, $article->favoritesCount());

        $article->toggleFavorite(1);
        $article->toggleFavorite(2);
        $article->toggleFavorite(3);

        $this->assertEquals(0, $article->favoritesCount());
    }

    /** @test */
    public function aModelKnowsWhichUsersHaveFavoritedHim()
    {
        $article = Article::first();

        $article->toggleFavorite(1);
        $article->toggleFavorite(2);
        $article->toggleFavorite(3);

        $this->assertEquals(3, $article->favoritedBy()->count());

        $article->toggleFavorite(1);
        $article->toggleFavorite(2);
        $article->toggleFavorite(3);

        $this->assertEquals(0, $article->favoritedBy()->count());
    }

    /** @test */
    public function aUserNotReturnFavoritesDeleteds()
    {
        $user = User::first();

        $article1 = Article::find(1);
        $article2 = Article::find(2);

        $user->addFavorite($article1);
        $user->addFavorite($article2);

        $article1->delete();

        $this->assertEquals(1, $user->favorite(Article::class)->count());
    }

    /** @test */
    public function aModelDeleteFavoritesOnDeletedObserver()
    {
        $user = User::find(1);
        $user2 = User::find(2);

        $article = Article::first();

        $user->addFavorite($article);
        $user2->addFavorite($article);

        $this->assertDatabaseHas(
            'favorites', [
                'user_id' => $user->id,
                'favoriteable_id' => $article->id,
                'favoriteable_type' => get_class($article),
            ]
        );

        $this->assertDatabaseHas(
            'favorites', [
                'user_id' => $user2->id,
                'favoriteable_id' => $article->id,
                'favoriteable_type' => get_class($article),
            ]
        );

        $article->delete();

        $this->assertDatabaseMissing(
            'favorites', [
                'user_id' => $user->id,
                'favoriteable_id' => $article->id,
                'favoriteable_type' => get_class($article),
            ]
        );

        $this->assertDatabaseMissing(
            'favorites', [
                'user_id' => $user2->id,
                'favoriteable_id' => $article->id,
                'favoriteable_type' => get_class($article),
            ]
        );
    }
}
