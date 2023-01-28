<?php

namespace App\Http\Controllers;

use App\Http\Requests\MovieRequest;
use App\Http\Resources\MovieResource;
use App\Models\Movie;
use App\Models\MovieFile;
use App\Models\MovieList;
use App\Repositories\MovieRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MovieController extends Controller
{
    private $movieRepository;

    public function __construct(MovieRepository $projects)
    {
        $this->movieRepository = $projects;
//        $this->middleware('cache.no');
    }

    final public function create(MovieRequest $request, MovieList $movieList): JsonResponse
    {
        $data = $request->validated();
        // create Movie
        $movie = $movieList->movies()->create($data);
        return $this->respondWithSuccess(['data' => [
            'movie' => $this->movieRepository->parse($movie),
            "message" => "Successfully created " . $movie->name
        ]], 201);
    }

    final public function show($id)
    {
        $movie = Movie::findOrFail($id);
        // $post_location = "posts";
        // $url = Storage::disk('s3')->url($post_location);
        return $this->respondWithSuccess(['data' => [
            'movie' => $this->movieRepository->parse($movie)
            ]], 201);
    }

    // final public function rando()
    // {
    //     $movie = Movie::inRandomOrder()->limit(1)->get();
    //     $data = MovieResource::collection($movie);
    //     return $this->respondWithSuccess($data);
    // }
    public function rando(Request $request)
    {
        $type = $request->query('type');
        $movie = null;

        try {
            if ($type === 'Series') {
                $movie = Movie::where('type', 'Series')
                                ->inRandomOrder()
                                ->first();
            } else {
                $movie = Movie::where('type', 'Movie')
                                ->inRandomOrder()
                                ->first();

            }
            return $this->respondWithSuccess(['movie' => $this->movieRepository->parse($movie)]);
        } catch (\Exception $e) {
            return response()->json($e, 500);
        }
    }


    final public function index(Request $request)
    {
        $movies = Movie::latest()->searchable();
        $data = MovieResource::collection($movies->items());
        return $this->respondWithSuccess(array_merge($movies->toArray(), ['data' => $data]));
    }

    final public function update(Request $request, Movie $movie)
    {
        $movie->update($request->all());
        return $this->respondWithSuccess(['data' => [
            'movie' => $this->movieRepository->parse($movie),
            "message" => "Successfully updated " . $movie->name]], 201);
    }

    final public function destroy($id)
    {
        $movie = Movie::destroy($id);
        return $this->respondWithSuccess('Deleted Successfully', 201);
    }

    final public function store(Request $request){
        $this->validate($request, ['url' => 'required']);
        if($request->hasfile('url')){
            $file = $request->file('url');
            $name = time().$file->getClientOriginalName();
            $filePath = 'movies/'.$name;
            Storage::disk('s3')->put($filePath, file_get_contents($file));
         }
         //insert into database
        $movie_files = new MovieFile();
        $movie_files->movie_id = $request->movie_id;
        $movie_files->url = $filePath;
        // $movie_file->thumbnail = $filePath;
        $movie_files->size = $request->size;
        $movie_files->duration = $request->duration;
        $movie_files->meta = $request->meta;
        $movie_files->year = $request->year;
        $movie_files->director = $request->director;
        $movie_files->age_rating = $request->age_rating;
        $movie_files->processed_at = Carbon::now();
        $movie_files->save();

        return $this->respondWithSuccess(['data' => ['message' => 'Movie inserted successfully', 'movie_file' =>   $movie_files]], 201);
    }

    final public function delete($id){
        $movie_files = MovieFile::findorfail($id);

        if(empty($movie_files)){
            return response()->json(['success' => false, 'message' => 'Movie file not found'], 404);
        }

        //We remove existing movie files
        if(!empty($movie_files))
        {
            Storage::disk('s3')->delete($movie_files->url);
            $movie_files->delete();
            return response()->json(['success' => true, 'message' => 'Movie file deleted Successfully'], 200);
        }

        return response()->json(['success' => false, 'message' => 'Unable to delete the Movie file. Please try again later.'], 400);
    }
    
}
