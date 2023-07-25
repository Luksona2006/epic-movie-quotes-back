<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class FriendResource extends JsonResource
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
        return [
            'id' => $this->id,
            'user' => new UserResource(User::findOrFail($this->user_id)),
            'friend' => new UserResource(User::findOrFail($this->friend_id)),
        ];
    }
}
