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

        $isAuth = auth('api')->check();
        $is_following = false;
        if ($isAuth) {
            $auth_id = auth('api')->id();
            $is_following = (bool)auth('api')->user()->following()->where('follower_id', $auth_id)->count();
        }
        $extraData = [
            "user" => $this->user,
            "likes" => $this->likes,
            "dislikes" => $this->dislikes,
            "followers" => $this->user->metrics["followers"],
            "following" => $this->user->metrics["following"],
            "is_following" => $is_following,
        ];

        
        return array_merge($parent, $extraData);
    }
}
