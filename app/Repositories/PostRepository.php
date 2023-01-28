<?php

namespace App\Repositories;

use App\Http\Resources\MovieResource;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\User;
use App\Repositories\BaseRepository;
use JetBrains\PhpStorm\Pure;

class PostRepository extends BaseRepository
{

    /**
     * ProjectRepository constructor.
     * @param Post $model
     */
    #[Pure] public function __construct(Post $model)
    {
        parent::__construct($model);
        $this->model = $model;
    }


    public function parse(Post $post)
    {
        return new PostResource($post);
    }
}
