<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Like;
use App\Http\Resources\MovieResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\CommentResource;
use App\Models\QuoteUser;

class QuoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public $preserveKeys = true;
    public static $wrap = 'quote';

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'movie_id' => $this->movie_id,
            'text' =>  $this->getTranslations('text'),
            'image' => $this->image,
            'likes' => $this->likes->count(),
            'liked' => QuoteUser::where('quote_id', $this->id)->where('user_id', auth()->id())->get()->count() > 0,
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'user' => new UserResource($this->whenLoaded('user')),
            'movie' => new MovieResource($this->whenLoaded('movie')),
            'commentsTotal' => $this->comments->count(),
        ];
    }
}
