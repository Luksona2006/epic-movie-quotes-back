<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\MovieResource;
use App\Http\Resources\CommentResource;
use App\Models\Like;

class QuoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public $preserveKeys = true;

    public function toArray($request)
    {
        $model = $this->find($this->id)->toArray();

        return [
            'id' => $this->id,
            'text' => [
                'en' => $model['text']['en'],
                'ka' => $model['text']['ka'],
            ],
            'image' => $this->image,
            'likes' => $this->likes->count(),
            'liked' => Like::where([['user_id', $this->user->id],['quote_id', $this->id]])->count() > 0,
            'commentsTotal' => $this->comments->count()
        ];
    }
}
