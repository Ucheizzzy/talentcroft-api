<?php

namespace App\Http\Controllers;

use App\Http\Resources\ListResource;
use App\Models\MovieList;
use App\Repositories\ListRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MovieListController extends Controller
{

    private $listRepository;

    public function __construct(ListRepository $lists)
    {
        $this->listRepository = $lists;
//        $this->middleware('cache.no');
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'type' => ['required', 'string'],
            'genre' => ['required', 'string'],
        ]);
        // create MovieList
        $list = getUser()->lists()->create($data);
        return $this->respondWithSuccess(['data' => [
            'list' => $this->listRepository->parse($list),
            "message" => "Successfully created " . $list->name
        ]], 201);
    }
    public function updateMovieList(Request $request, $id)
    {
        $movielist = MovieList::findorfail($id)->update([
            'title' =>$request->title,
            'description' =>$request->description,
            'type'=>$request->type,
            'genre'=>$request->genre,
            'created_at' =>Carbon::now(),
        ]);

        return $this->respondWithSuccess(['data' => ['message' => 'Movie list updated successfully', 'movielist' =>   $movielist]], 201);
    }

    public function index(Request $request)
    {
        $typeQuery = $request->query('type');
        $genreQuery = $request->query('genre');

        $list = [];

        try {
            if ($typeQuery) {
                if ($genreQuery) {
                    $list = MovieList::where('type', $typeQuery)
                                ->where('genre', $genreQuery)
                                ->inRandomOrder()
                                ->get();
                } else {
                    $list = MovieList::where('type', $typeQuery)
                                ->inRandomOrder()
                                ->get();
                }
            } else {
                $list = MovieList::all();
            }
            $data = ListResource::collection($list);
            return $this->respondWithSuccess(['message' => $data[0]->title, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json($e, 500);
        }
    }

    final public function show($id)
    {
        $list = MovieList::findOrFail($id);
        return $this->respondWithSuccess([
            'list' => $this->listRepository->parse($list)
            ], 201);
    }


    public function getAllMovieList(){
        $list = MovieList::latest()->get();
        $data = ListResource::collection($list);
        return $this->respondWithSuccess(['message' => 'All Movielist', 'data' => $data]);

    }

    public function deleteMovieList($id){
       $deletemovielist= MovieList::findorfail($id)->delete();
        return $this->respondWithSuccess(['data' => ['message' => 'Movielist deleted successfully ', 'deletemovielist' =>   $deletemovielist]], 201);
    }
}
