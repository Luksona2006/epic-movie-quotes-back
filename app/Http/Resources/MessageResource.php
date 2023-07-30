<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */

    public $preserveKeys = true;
    public static $wrap = 'message';

    public function toArray($request): array
    {
        return [
            'user_id' => $this->pivot->user_id ?? $this->user_id,
            'text' => $this->pivot->text ?? $this->text,
        ];
    }
}
