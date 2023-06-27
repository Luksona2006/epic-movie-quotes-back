<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\QuoteResource;
use App\Http\Resources\GenreResource;

class MovieResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public $preserveKeys = true;

    public function toArray($request): array
    {
        $model = $this->find($this->id)->toArray();

        return [
            'id' => $this->id,
            'name' => [
                'en' => $model['name']['en'],
                'ka' => $model['name']['ka']
            ],
            'director' => [
                'en' => $model['director']['en'],
                'ka' => $model['director']['ka']
            ],
            'description' => [
                'en' => $model['description']['en'],
                'ka' => $model['description']['ka']
            ],
            'year' => $this->year,
            'image' => $this->image,
        ];
    }
}
