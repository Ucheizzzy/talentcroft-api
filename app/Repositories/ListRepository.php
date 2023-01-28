<?php

namespace App\Repositories;

use App\Http\Resources\ListResource;
use App\Http\Resources\MovieResource;
use App\Models\Movie;
use App\Models\MovieList;
use App\Models\User;
use App\Repositories\BaseRepository;
use JetBrains\PhpStorm\Pure;

class ListRepository extends BaseRepository
{

    /**
     * ProjectRepository constructor.
     * @param Movie $model
     */
    #[Pure] public function __construct(MovieList $model)
    {
        parent::__construct($model);
        $this->model = $model;
    }


    public function parse(MovieList $list)
    {
        return new ListResource($list);
    }
}
