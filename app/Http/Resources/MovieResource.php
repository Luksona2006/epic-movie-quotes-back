<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
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
    public static $wrap = 'movie';

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslations('name'),
            'director' => $this->getTranslations('director'),
            'description' => $this->getTranslations('description'),
            'year' => $this->year,
            'image' => $this->image,
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
            'quotes' => QuoteResource::collection($this->whenLoaded('quotes')),
            'quotesTotal' => $this->quotes->count(),
        ];
    }
}
