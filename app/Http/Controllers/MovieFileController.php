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
        $prev = $request->preview;
        $thumb = $request->thumbnail;
        $alt_thumb = $request->alt_thumbnail;
        $bg_picture = $request->background_picture;
        $title_image = $request->title_image;
        $movie_location = "movies";
        $preview_location = "preview";
        $thumbnail_location = "thumbnails";
        $altThumbnail_location = "alt_thumbnails";
        $bgPicture_location = "background_picture";
        $titleImage_location = "title_image";
        $aws = env('AWS_ROUTE');
        $movieFile = $movie->episodes()->create($request->only(['name', 'description', 'age_rating', 'director', 'genre']));
        // dd($movie->id, $movieFile->id);
        if ($file){
        $path = $file->storeAs($movie->id, "video/$movieFile->id.{$file->extension()}", 'processing');
        }
        if ($prev){
            $path2 = $prev->storeAs($movie->id, "preview/$movieFile->id.{$prev->extension()}", 'processing');
        }
        if ($thumb){
            $path3 = $thumb->storeAs($movie->id, "thumbnail/$movieFile->id.{$thumb->extension()}", 'processing');
        }
        if ($alt_thumb){
            $path4 = $alt_thumb->storeAs($movie->id, "altThumbnail/$movieFile->id.{$alt_thumb->extension()}", 'processing');
        }
        if ($bg_picture){
            $path5 = $bg_picture->storeAs($movie->id, "background/$movieFile->id.{$bg_picture->extension()}", 'processing');
        }
        if ($title_image){
            $path6 = $title_image->storeAs($movie->id, "title_image/$movieFile->id.{$title_image->extension()}", 'processing');
        }
        
        
        // $file_size = $file->getSize();
        // $movie_id = $movie->id;
        // $file_id = $movieFile->id;
        // $destination = "$movie_id/$file_id/";
        // $thumbnails = [];
        if ($file){
        $media = FFMpeg::fromDisk("processing")->open($path);
        $duration = $media->getDurationInSeconds();
        }
        if ($prev){
        $preview_media = FFMpeg::fromDisk("processing")->open($path2);
        }
        if ($thumb){
        $thumbnail_media = FFMpeg::fromDisk("processing")->open($path3);
        }
        if ($alt_thumb){
        $altthumbnail_media = FFMpeg::fromDisk("processing")->open($path4);
        }
        if ($bg_picture){
        $bgPicture_media = FFMpeg::fromDisk("processing")->open($path5);
        }
        if ($title_image){
            $titleImage_media = FFMpeg::fromDisk("processing")->open($path5);
        }
        
        // $round = round($duration * 0.25);
        if ($file) { 
            $filename = "$movie_location/$movieFile->id.{$file->extension()}"; 
        } else {
            $filename = "";
        }
        if ($prev) {
            $preview = "$preview_location/$movieFile->id.{$prev->extension()}";
        } else {
            $preview = "";
        }
        if ($thumb) {
            $thumbnail = "$thumbnail_location/$movieFile->id.{$thumb->extension()}";
        } else {
            $thumbnail = "";
        }
        if ($alt_thumb) {
            $altThumbnail = "$altThumbnail_location/$movieFile->id.{$alt_thumb->extension()}";
        } else {
            $altThumbnail = "";
        }
        // $media = $media->getFrameFromSeconds($round)->export()->toDisk("s3")->save($thumbnail);
        if ($bg_picture) {
            $bg = "$bgPicture_location/$movieFile->id.{$bg_picture->extension()}";
        } else {
            $bg = "";
        }
        if ($title_image) {
            $title_img = "$titleImage_location/$movieFile->id.{$title_image->extension()}";
        } else {
            $title_img = "";
        }
        // dd($thumbnail, $altThumbnail, $preview, $filename, $path, $path2, $path3, $path4);
        $movieFile->update([
            'alt_thumbnail' => "$aws/$altThumbnail",
            'thumbnail' => "$aws/$thumbnail",
            'duration' => $duration,
            "video" => "$aws/$filename",
            "preview" => "$aws/$preview",
            "background_picture" => "$aws/$bg",
            "title_img" => "$aws/$title_img"
        ]);
        if ($file) $filevideo = $media->export()
            ->toDisk('s3')
            ->save($filename);
        if ($prev) $filepreview = $preview_media->export()
            ->toDisk('s3')
            ->save($preview);
        if ($thumb) $filethumbnail = $thumbnail_media->export()
            ->toDisk('s3')
            ->save($thumbnail);
        if ($alt_thumb) $filealtthumbnail = $altthumbnail_media->export()
            ->toDisk('s3')
            ->save($altThumbnail);
        if ($bg_picture) $filebgPicture = $bgPicture_media->export()
            ->toDisk('s3')
            ->save($altThumbnail);
        if ($title_image) $filetitleImage = $titleImage_media->export()
            ->toDisk('s3')
            ->save($title_img);
        // UPDATE file processed at and filesize
        $movieFile->update(['processed_at' => now(), //'size' => $file_size / 1048576
    ]);
        //remove $media created files
        if ($file) $filevideo->cleanupTemporaryFiles();
        if ($prev) $filepreview->cleanupTemporaryFiles();
        if ($thumb) $filethumbnail->cleanupTemporaryFiles();
        if ($alt_thumb) $filealtthumbnail->cleanupTemporaryFiles();
        if ($bg_picture) $filebgPicture->cleanupTemporaryFiles();
        if ($title_image) $filetitleImage->cleanupTemporaryFiles();
        
        // Delete file used for processing
        if ($file) Storage::disk("processing")->delete($path);
        if ($prev) Storage::disk("processing")->delete($path2);
        if ($thumb) Storage::disk("processing")->delete($path3);
        if ($alt_thumb) Storage::disk("processing")->delete($path4);
        if ($bg_picture) Storage::disk("processing")->delete($path5);
        if ($title_image) Storage::disk("processing")->delete($path6);

        return response()->json(['success' => true, 'message' => 'Movies successfully uploaded', 'file' =>$movieFile], 200);
    }

    public function updateFile(Request $request, $movieFileId)
    {
    $movieFile = MovieFile::find($movieFileId);
    $file = $request->video;
    $prev = $request->preview;
    $thumb = $request->thumbnail;
    $alt_thumb = $request->alt_thumbnail;
    $bg_picture = $request->background_picture;
    $aws = env('AWS_ROUTE');
    $movie_location = "movies";
    $preview_location = "preview";
    $thumbnail_location = "thumbnails";
    $altThumbnail_location = "alt_thumbnails";
    $bgPicture_location = "background_picture";

    if ($file) {

        $duration = $movieFile->duration;
        $movie_location = "movies";
        $thumbnail_location = "thumbnails";
        $preview_location = "preview";
        $thumbnail = "$thumbnail_location/$movieFile->id/thumbnail.png";
        Storage::disk('s3')->delete($thumbnail);
        // $ext = $movie->video->extension();
        // dd("$movie_location/$movie->id");
        $filename = "$movie_location/$movieFile->id.mp4";
        Storage::disk('s3')->delete($filename);
        $movieFile->delete();


        $path = $file->storeAs($movieFile->movie_id, "video/$movieFile->id.{$file->extension()}", 'processing');
        $media = FFMpeg::fromDisk("processing")->open($path);
        $duration = $media->getDurationInSeconds();
        $filename = "$movie_location/$movieFile->id.{$file->extension()}";
        $media->export()->toDisk('s3')->save($filename);
        $movieFile->update(['video' => "$aws/$filename", 'duration' => $duration]);
        // $filevideo->cleanupTemporaryFiles();
    } else {
        $duration = $movieFile->duration;
        $filename = $movieFile->video;
    }

    if ($prev) {


        $duration = $movieFile->duration;
        $movie_location = "movies";
        $preview_location = "preview";
        // $ext = $movie->video->extension();
        // dd("$movie_location/$movie->id");
        $preview = "$preview_location/$movieFile->id.mp4";
        Storage::disk('s3')->delete($preview);
        $movieFile->delete();


        $path2 = $prev->storeAs($movieFile->movie_id, "preview/$movieFile->id.{$prev->extension()}", 'processing');
        $preview_media = FFMpeg::fromDisk("processing")->open($path2);
        $preview = "$preview_location/$movieFile->id.{$prev->extension()}";
        $preview_media->export()->toDisk('s3')->save($preview);
        $movieFile->update(['preview' => "$aws/$preview"]);
        // $filepreview->cleanupTemporaryFiles();
    } else {
        $preview = $movieFile->preview;
    }

    if ($thumb) {

        $thumbnail_location = "thumbnails";
        $thumbnail = "$thumbnail_location/$movieFile->id/thumbnail.png";
        Storage::disk('s3')->delete($thumbnail);

        $path3 = $thumb->storeAs($movieFile->movie_id, "thumbnail/$movieFile->id.{$thumb->extension()}", 'processing');
        $thumbnail_media = FFMpeg::fromDisk("processing")->open($path3);
        $thumbnail = "$thumbnail_location/$movieFile->id.{$thumb->extension()}";
        $thumbnail_media->export()->toDisk('s3')->save($thumbnail);
        $movieFile->update(['thumbnail' => "$aws/$thumbnail"]);
        // $filethumbnail->cleanupTemporaryFiles();
    } else {
        $thumbnail = $movieFile->thumbnail;
    }

    if ($alt_thumb) {

        $altThumbnail_location = "alt_thumbnails";
        $altThumbnail = "$altThumbnail_location/$movieFile->id.png";
        Storage::disk('s3')->delete($altThumbnail);

        $path4 = $alt_thumb->storeAs($movieFile->movie_id, "altThumbnail/$movieFile->id.{$alt_thumb->extension()}", 'processing');
        $altthumbnail_media = FFMpeg::fromDisk("processing")->open($path4);
        $altThumbnail = "$altThumbnail_location/$movieFile->id.{$alt_thumb->extension()}";
        $altthumbnail_media->export()->toDisk('s3')->save($altThumbnail);
        $movieFile->update(['alt_thumbnail' => "$aws/$altThumbnail"]);
        // $filealtthumbnail->cleanupTemporaryFiles();
    } else {
        $altThumbnail = $movieFile->alt_thumbnail;
    }

    if ($bg_picture) {

        $bgPicture_location = "background_picture";
        $bg = "$bgPicture_location/$movieFile->id.png";
        Storage::disk('s3')->delete($bg);

        $path5 = $bg_picture->storeAs($movieFile->movie_id, "background/$movieFile->id.{$bg_picture->extension()}", 'processing');
        $bgPicture_media = FFMpeg::fromDisk("processing")->open($path5);
        $bg = "$bgPicture_location/$movieFile->id.{$bg_picture->extension()}";
        $bgPicture_media->export()->toDisk('s3')->save($bg);
        $movieFile->update(['background_picture' => "$aws/$bg"]);
        // $filebgPicture->cleanupTemporaryFiles();
        } else {
        $bg = $movieFile->background_picture;
        }

            // update any other fields as needed
                if($request->description) $movieFile->description = $request->description;
                if($request->name) $movieFile->name = $request->name;
                if($request->director) $movieFile->director = $request->director;
                if($request->age_rating) $movieFile->age_rating = $request->age_rating;
                if($request->genre) $movieFile->genre = $request->genre;
        
            // save the changes
            $movieFile->save();

        
        return $this->respondWithSuccess(['message' => 'Movie file has been updated successfully!', 'data' => $movieFile]);
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
