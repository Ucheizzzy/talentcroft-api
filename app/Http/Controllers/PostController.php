<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Reaction;
use Illuminate\Http\Request;
use App\Models\PostSentiment;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\PostRequest;
use App\Http\Resources\PostResource;
use App\Repositories\PostRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class PostController extends Controller
{

    private $postRepository;

    public function __construct(PostRepository $posts)
    {
        $this->postRepository = $posts;
//        $this->middleware('cache.no');
    }

    public function getAllPost(){
        $url = Storage::disk('s3')->files('posts');
        $post = Post::latest()->get();
        $data = PostResource::collection($post);
        return $this->respondWithSuccess(['data' => $data]);
    }

    final public function getMyPosts(Request $request)
    {
        $posts = $request->user()->posts()->searchable();
        $data = PostResource::collection($posts->items());
        return $this->respondWithSuccess(array_merge($posts->toArray(), ['data' => $data]));
    }

    final public function getOthersPosts(Request $request, User $user)
    {
        $posts = $user->posts()->searchable();
        $data = PostResource::collection($posts->items());
        return $this->respondWithSuccess(array_merge($posts->toArray(), ['data' => $data]));
    }

    final public function likeProject(Post $post, User $user)
    {
        //Delete if exist
        $sentiment = $post->sentiments()->where(['user_id' => auth()->id()])->first();
        if ($sentiment && $sentiment->isLiked) {
            $sentiment->delete();
            return $this->respondWithSuccess('delete');
        }
        $liked = $post->sentiments()->updateOrCreate(['user_id' => auth()->id()], ['sentiment' => 'liked']);
        return $this->respondWithSuccess('liked');
    }

    final public function dislikeProject(Post $post)
    {
        $sentiment = $post->sentiments()->where(['user_id' => auth()->id()])->first();
        if ($sentiment && $sentiment->isDisliked) {
            $sentiment->delete();
            return $this->respondWithSuccess('delete');
        }
        $post->sentiments()->updateOrCreate(['user_id' => auth()->id()], ['sentiment' => 'disliked']);
        return $this->respondWithSuccess('disliked');
    }

    final public function getProjectSentiments()
    {
        $sentiment = PostSentiment::all();
        return $this->respondWithSuccess(['data' => ['message' => 'Likes', 'sentiment' => $sentiment]], 201);
    }

    public function getMySentiment($id)
    {
        $sentiment = PostSentiment::findOrFail($id);
        return $this->respondWithSuccess(['data' => ['message' => 'sentiment  ' . $sentiment->id . ' is found', 'sentiment' => $sentiment]], 201);
    }

    public function random()
    {
        $posts = Post::inRandomOrder()->take(13)->get();
        $data = PostResource::collection($posts);
        return $this->respondWithSuccess(['data' => $data]);
        // return $this->respondWithSuccess(['data' => [
        //     'message' => '10 random posts made by users', 
        //     'posts' =>  $this->postRepository->parse($posts),
        //     ]
        // ], 201);
    }

    // public function getTimeline($id)
    // {
    //     //get a user by Id
    //     $user = User::findOrFail($id);
    //     // Get posts by user
    //     $posts = Post::where('user_id', $user->id)
    //         ->with(['user_id' => function ($query) {
    //             $query->select('email', 'profilePicture', 'username', 'createdAt', 'id');
    //         }])->get();

    //     // Get posts by users you follow
    //     $communityPost = collect();
    //     foreach ($user->following as $communityId) {
    //         $communityPost = $communityPost->concat(Post::where('_creator', $communityId)
    //         ->with(['_creator' => function ($query) {
    //             $query->select('email', 'profilePicture', 'username', 'createdAt', 'id');
    //         }])->get());
    //     }
    //     return response()->json($posts->concat($communityPost));
        
    // }

    public function show($id)
    {
        $post = Post::findOrFail($id);
        return $this->respondWithSuccess(['data' => [
            'post' => $this->postRepository->parse($post)
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
        $path = $file->storeAs($post_location, "$post_id.{$file->extension()}" , 'posts'); 
        $media = FFMpeg::fromDisk("posts")->open($path);
        $filename = "$post_location/$post->id.{$file->extension()}";
        $post->update([
            'url' => "$aws/$filename",
        ]);
        $media = $media->export()
            ->toDisk('s3')
            ->save($filename);

        //remove $media created files
        $media->cleanupTemporaryFiles();
        
        // Delete file used for processing
        Storage::disk("posts")->delete($path);
        return response()->json(['success' => true, 'message' => 'Post successfully uploaded', 'post' => $post], 200);
    }

    final public function destroy($id)
    {
        $post = Post::findorfail($id);
        // $post = Post::destroy($id);
        if(empty($post)){
            return response()->json(['success' => false, 'message' => 'Post not found'], 404);
        }
        if(!empty($post)){
            $post_location = "posts";
            $filename = "$post_location/$post->id";
            // $ext = $movie->video->extension();
            // dd("$movie_location/$movie->id");
            Storage::disk('s3')->delete($filename);
            $post->delete();
            return response()->json(['success' => true, 'message' => 'Post'.'_'.$post->id.'_'.'deleted'], 200);
        }
        return response()->json(['message' => 'Unable to delete movie. Please try again later.'], 400);
    }

       public function createReaction(Request $request,  User $user, $post_id): JsonResponse
    {
        
        $reaction = $request->validate([
            'caption' => 'required',
            // 'video' => 'mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4,|required|max:102400',
        ]);

        $post = Post::findorfail($post_id);
        $post_link = $post->id;

        $reaction = new Reaction();
        $reaction->user_id = $request->user_id;
        $reaction->post_id = $post_link;
        $reaction->caption = $request->caption;
        $reaction->video = $request->video;
        $reaction_created_at = Carbon::now();

        $reaction->save(); 
     
        // reaction video file
        $user_id = $reaction->user_id;
        $reaction_location = "reactions";
        $aws = env('AWS_ROUTE');
        $file = $request->video;
        $reaction_id = $reaction->id;
        $path = $file->storeAs($reaction_location, "$reaction_id.{$file->extension()}" , 'reactions'); 
        $media = FFMpeg::fromDisk("reactions")->open($path);
        $filename = "$reaction_location/$reaction->id.{$file->extension()}";
        $reaction->update([
            'video' => "$aws/$filename",
        ]);
        $media = $media->export()
            ->toDisk('s3')
            ->save($filename);

        //remove $media created files
        $media->cleanupTemporaryFiles();
        
        // Delete file used for processing
        Storage::disk("reactions")->delete($path);
        return response()->json(['success' => true, 'message' => 'Reaction successfully uploaded', 'reaction' => $reaction], 200);
    }


}
