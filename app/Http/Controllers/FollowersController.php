<?php

namespace App\Http\Controllers;

use App\Models\Followers;
use Illuminate\Http\Request;

class FollowersController extends Controller
{
    public function getAllFollowers(){
        // $post = Post::getpost();
        $follower = Followers::latest()->get();
        return $this->respondWithSuccess(['data' => ['message' => 'All follower', 'follower' => $follower]], 201);
    }

    public function show($id)
    {
        $follower = Followers::findOrFail($id);
        return $this->respondWithSuccess(['data' => ['follower' => $follower]], 201);
    }
}
