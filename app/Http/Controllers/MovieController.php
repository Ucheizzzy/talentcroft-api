<?php

namespace App\Http\Controllers;

use App\Http\Requests\MovieRequest;
use App\Http\Resources\MovieResource;
use App\Models\Movie;
use App\Models\MovieFile;
use App\Repositories\MovieRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MovieController extends Controller
{
    public function uploadmovie(Request $request){
        $request->validate([
            'movie_id'=> 'required',
            'url'=> 'mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4,|required|max:102400',
            // 'size'=>'required|max:102400'
            // 'duration'=>'required',
        ]);

        // a folder movies_folder will be created inside the s3 bucket that we will specify in the .env file
         $base_location = 'movies_folder';

        // Handle File Upload
        if($request->hasFile('movies')) {                       
            $moviePath = $request->file('movies')->store($base_location, 's3');
          
        } else {
            return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
        }
    
        //We save new path
        $movies = new MovieFile();
        $movies->movie_id = $request->movie_id;
        $movies->url = $moviePath;
        $movies->thumbnail = $moviePath;
        $movies->size = $request->size;
        $movies->duration = $request->duration;
        $movies->meta = $request->meta;
        $movies->processed_at = Carbon::now();
    
        $movies->save();
       
        return response()->json(['success' => true, 'message' => 'Movies successfully uploaded', 'movies' =>$movies], 200);
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


    
}
