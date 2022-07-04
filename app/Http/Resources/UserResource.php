<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'user_id' => $this->user_id,
            'role' => $this->getRole->name,
            'name' => $this->name,
            'surname' => $this->surname,
            'number' => $this->number,
            'address' => $this->address,
            'email' => $this->email,
            'birthday' => $this->birthday,
            'login' => $this->login,
            'is_active' => $this->is_active,
        ];
    }
}
