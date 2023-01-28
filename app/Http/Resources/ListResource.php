<?php

namespace App\Http\Resources;

use App\Enums\TransactionStatus;
use App\Models\Movie;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use JsonSerializable;

class ListResource extends JsonResource
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
            "movie_count" => $this->movies->count(),
            "movies" => $this->movies,
            "files" => $this->files
        ];
        return array_merge($parent, $extraData);
    }
}
