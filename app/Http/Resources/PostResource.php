<?php

namespace App\Http\Resources;

use App\Enums\TransactionStatus;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use JsonSerializable;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        $parent = parent::toArray($request);
        $extraData = [
            "user" => $this->user,
        ];

        
        return array_merge($parent, $extraData);
    }
}
