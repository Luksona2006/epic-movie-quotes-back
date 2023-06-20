<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;
use App\Models\Movie;
use App\Models\User;
use App\Models\Like;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection as SupportCollection;

class Quote extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = ['id'];

    public $translatable = ['text'];

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function getFullData(): SupportCollection
    {
        $comments = $this->comments;

        $commentsWithUsers = $comments->map(function ($comment) {
            return ['user' => $comment->user, ...$comment->toArray()];
        });


        $likes = $this->likes->toArray();
        $likesSum = count($likes);
        $liked = count(array_filter($likes, function ($like) {
            return $like['user_id'] === $this->user->id;
        })) ? true : false;

        return collect([...$this->toArray() ,'movie' => $this->movie, 'author' => $this->user ,'comments' => $commentsWithUsers, 'likes' => $likesSum, 'liked' => $liked]);
    }
}
