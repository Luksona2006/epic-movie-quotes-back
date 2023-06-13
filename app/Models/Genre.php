<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;
use App\Models\Movie;
use App\Models\MovieGenre;

class Genre extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = ['id'];

    public $translatable = ['name'];

    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class)->using(MovieGenre::class);
    }
}
