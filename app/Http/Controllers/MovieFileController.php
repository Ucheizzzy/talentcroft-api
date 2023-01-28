<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\MovieFile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class MovieFileController extends Controller
{

    public function show($id)
    {
        $file = MovieFile::findOrFail($id);
        return $this->respondWithSuccess(['data' => ['movieFile' => $file]], 201);
    }


    public function uploadmovie(Request $request, Movie $movie)
    {
        $file = $request->video;
        // dd($file);
        $movie_location = "movies";
        $preview_location = "preview";
        $thumbnail_location = "thumbnails";
        $aws = env('AWS_ROUTE');
        $movieFile = $movie->episodes()->create($request->only(['name', 'description', 'age_rating', 'director', 'genre']));
        // dd($movie->id, $movieFile->id);
        $path = $file->storeAs($movie->id, "$movieFile->id.{$file->extension()}", 'processing');
        
        // $file_size = $file->getSize();
        $movie_id = $movie->id;
        $file_id = $movieFile->id;
        // $destination = "$movie_id/$file_id/";
        $thumbnails = [];
        $media = FFMpeg::fromDisk("processing")->open($path);
        $duration = $media->getDurationInSeconds();
        $round = round($duration * 0.25);
        $thumbnail = "$thumbnail_location/$file_id/thumbnail.png";
        $media = $media->getFrameFromSeconds($round)->export()->toDisk("s3")->save($thumbnail);
        $filename = "$movie_location/$movieFile->id.{$file->extension()}";
        $preview = "$preview_location/$movieFile->id.{$file->extension()}";
        // dd($thumbnails);
        $movieFile->update([
            'thumbnail' => "$aws/$thumbnail",
            'duration' => $duration,
            "video" => "$aws/$filename",
            // "preview" => "$aws/$preview"
        ]);
        $media = $media->export()
            ->toDisk('s3')
            ->save($filename);
        // $media = $media->export()
        //     ->toDisk('s3')
        //     ->save($filename);
        // UPDATE file processed at and filesize
        $movieFile->update(['processed_at' => now(), //'size' => $file_size / 1048576
    ]);
        //remove $media created files
        $media->cleanupTemporaryFiles();
        
        // Delete file used for processing
        Storage::disk("processing")->delete($path);

        return response()->json(['success' => true, 'message' => 'Movies successfully uploaded', 'file' =>$movieFile], 200);
    }

    final public function index(Request $request)
    {
        $files = MovieFile::latest()->searchable();
        return $this->respondWithSuccess(array_merge(['data' => $files]));
    }

     public function destroy($id)
    {
        $movie = MovieFile::findorfail($id);

        if(empty($movie)){
            return response()->json(['success' => false, 'message' => 'Movie not found'], 404);
        }

        //We remove existing movie
        if(!empty($movie)){
            $duration = $movie->duration;
            $movie_location = "movies";
            $thumbnail_location = "thumbnails";
            $preview_location = "preview";
            $thumbnail = "$thumbnail_location/$movie->id/thumbnail.png";
            Storage::disk('s3')->delete($thumbnail);
            // $ext = $movie->video->extension();
            // dd("$movie_location/$movie->id");
            $filename = "$movie_location/$movie->id";
            $preview = "$preview_location/$movie->id";
            Storage::disk('s3')->delete($filename);
            // Storage::disk('s3')->delete($preview);
            $movie->delete();
            return response()->json(['success' => true, 'message' => 'Movie deleted'], 200);
        }

        return response()->json(['success' => false, 'message' => 'Unable to delete movie. Please try again later.'], 400);
    }
}
