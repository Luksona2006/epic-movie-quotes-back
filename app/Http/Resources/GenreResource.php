<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GenreResource extends JsonResource
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
            ]
        ];
    }
}
