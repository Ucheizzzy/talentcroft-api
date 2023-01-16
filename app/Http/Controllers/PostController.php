<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function getAllPost(){
        // $post = Post::getpost();
        $url = Storage::disk('s3')->files('posts');
        $post = Post::latest()->get();
        // dd($url, $post[0]->id);
        return $this->respondWithSuccess(['data' => [
            'message' => 'All post made by users', 
            'post' => $post,
            // 'id' => $post->id,
            // 'user_id' => $post->user_id,
            // 'description' => $post->description,
            // 'url' => $url
            ]
        ], 201);
    }

    public function show($id)
    {
        $post = Post::findOrFail($id);
        $post_location = "posts";
        $url = Storage::disk('s3')->url($post_location);
        return $this->respondWithSuccess(['data' => [
            // 'post' => $post
            'id' => $post->id,
            'user_id' => $post->user_id,
            'description' => $post->description,
            'url' => "$url/$post->id.mp4"
            ]], 201);
    }

    public function createPost(PostRequest $request, User $user): JsonResponse
    {
        

        $data = $request->validated();
        // create Post
        
        $post = getUser()->posts()->create($data);

        // url file
        $user_id = $post->user_id;
        $post_location = "posts";
        $aws = env('AWS_ROUTE');
        $file = $request->url;
        $post_id = $post->id;
        $path = $file->storeAs($post_location, "$post->id.{$file->extension()}" , 's3'); 
        return $this->respondWithSuccess([
            'posts' => ["id" => $post_id, "user_id" => $user_id, "description" => $post->description, "url" => "$aws/$path", "message" => "Successfully created " . $post->id]], 201);
    }

    final public function destroy($id)
    {
        $post = Post::destroy($id);
        return $this->respondWithSuccess(['data' => ['message' => 'post'.'_'.$id.'_'.'deleted' ,'post' => $post]], 201);
    }
}
