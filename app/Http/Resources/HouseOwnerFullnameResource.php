<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\House;

class HouseOwnerFullnameResource extends JsonResource
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
            'house_id' => $this->house_id,
            'user_id' => $this->user_id,
            'fullname' => $this->getHouseOwner->name . ' ' . $this->getHouseOwner->surname,
            'name' => $this->name,
            'address' => $this->address,
            'count_rooms' => $this->count_rooms,
            'count_windows' => $this->count_windows,
            'count_doors' => $this->count_doors,
        ];
    }
}
