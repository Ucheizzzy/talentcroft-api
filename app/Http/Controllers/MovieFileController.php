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
    public function uploadmovie(Request $request, Movie $movie)
    {
        $file = $request->video;
        // dd($file);
        $movie_location = "movies";
        $thumbnail_location = "thumbnails";
        $aws = env('AWS_ROUTE');
        $movieFile = $movie->episodes()->create($request->only(['name', 'decription', 'age_rating', 'director', 'genre']));
        // dd($movie->id, $movieFile->id);
        $path = $file->storeAs($movie->id, "$movieFile->id.{$file->extension()}", 'processing');
        
        // $file_size = $file->getSize();
        $movie_id = $movie->id;
        $file_id = $movieFile->id;
        // $destination = "$movie_id/$file_id/";
        $thumbnails = [];
        $media = FFMpeg::fromDisk("processing")->open($path);
        $duration = $media->getDurationInSeconds();
        $rounds = [round($duration * 0.1), round($duration * 0.25), round($duration * 0.5), round($duration * .75), round($duration * .90)];

        foreach ($rounds as $second => $key) {
            $thumbnail = "$thumbnail_location/$file_id/thumbnail_{$key}.png";
            $thumbnails[] = "$thumbnail";
            $media = $media->getFrameFromSeconds($second)->export()->toDisk("s3")->save($thumbnail);
        }
        $filename = "$movie_location/$movie->id.{$file->extension()}";
        $movieFile->update([
            'thumbnail' => "$aws/$thumbnails[0]",
            'duration' => $duration,
            "video" => "$aws/$filename",
        ]);

        $media = $media->export()
        //     ->addFormat($lowBitrate, function ($media) {
        //         $media->addFilter('scale=640:480');
        //     })
        //     ->addFormat($midBitrate, function ($media) {
        //         $media->scale(960, 720);
        //     })
        //     ->addFormat($highBitrate, function ($media) {
        //         $media->addFilter(function ($filters, $in, $out) {
        //             $filters->custom($in, 'scale=1920:1200', $out); // $in, $parameters, $out
        //         });
        //     })
        //     ->addFormat($superBitrate, function ($media) {
        //         $media->addLegacyFilter(function ($filters) {
        //             $filters->resize(new Dimension(2560, 1920));
        //         });
        //     })
            // ->onProgress(function ($percentage) use ($movieFile) {
            //     // UPDATE file process progress
            //     $movieFile->update(['progress' => $percentage]);
            // })
            ->toDisk('s3')
            ->save($filename);
        // UPDATE file processed at and filesize
        $movieFile->update(['processed_at' => now(), //'size' => $file_size / 1048576
    ]);
        //remove $media created files
        $media->cleanupTemporaryFiles();
        
        // Delete file used for processing
        Storage::disk("processing")->delete($path);

        return response()->json(['success' => true, 'message' => 'Movies successfully uploaded', 'file' =>$movieFile], 200);
    }

     public function destroy($id)
    {
        $movie = MovieFile::findorfail($id);

        if(empty($movie)){
            return response()->json(['success' => false, 'message' => 'Movie not found'], 404);
        }

        //We remove existing movie
        if(!empty($movie)){
            $movie_location = "movies";
            $filename = "$movie_location/$movie->id.mp4";
            Storage::disk('s3')->delete($filename);
            $movie->delete();
            return response()->json(['success' => true, 'message' => 'Movie deleted'], 200);
        }

        return response()->json(['success' => false, 'message' => 'Unable to delete movie. Please try again later.'], 400);
    }
}
