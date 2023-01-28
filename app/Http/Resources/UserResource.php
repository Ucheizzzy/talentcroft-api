<?php

namespace App\Http\Resources;

use App\Enums\TransactionStatus;
use App\Models\Movie;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use JsonSerializable;

class UserResource extends JsonResource
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
            "post_count" => $this->posts->count(),
            "posts" => $this->posts
        ];
        return array_merge($parent, $extraData);
    }
}
