<?php
header('Accept: application/json', true);
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization, Accept,charset,boundary,Content-Length');
header('Access-Control-Allow-Credentials: true');

use App\Http\Controllers\{
    UserController,
    PaymentController,
    ProjectController,
    SettingsController,
    ApiController,
    MovieListController,
    MovieController,
    MovieFileController,
    PostController
};
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//public routes
    /** AUTHENTICATION AND USER DETAILS */
    Route::group(['prefix' => 'user'], function () {
        // login
        Route::post('/login', [UserController::class, 'login']);
        // register
        Route::post('/register', [UserController::class, 'register']);
        Route::post('/admin/createUser', [UserController::class, 'createUser']);
        Route::group(['middleware' => 'auth:api'], function () {
            // get authenticated user
             Route::get('/profile', [UserController::class, 'getAuthenticatedUser']);
            // userstats
            Route::get('/stats', [UserController::class,'userStats']);
            // update user credentials
            Route::put('/update/{id}', [UserController::class, 'updateUser']);
            // delete user
            Route::delete('/{id}', [UserController::class, 'deleteUser']);

            Route::get('/follow/{user:id}', [UserController::class, 'followUser']);
            
        });
   
        //get all users
        Route::get('/allusers', [UserController::class,'getAllUsers']);
        //get a user
        Route::get('/{id}', [UserController::class,'getUser']);
        // forgetpassword
        Route::post('/forgetpassword', [UserController::class, 'forgetpassword']);
        // reset password
         Route::post('/resetpassword', [UserController::class, 'resetpassword']);

         Route::get('/followers', [UserController::class, 'getFollowers']);

    });
   
    Route::group(['middleware' => ['api', 'cors'], 'prefix' => 'post'], function () {
         // find all post
        Route::get('/allpost', [PostController::class, 'getAllPost']);
        // find 10 random posts
        Route::get('/random', [PostController::class, 'random']);
        // find one post
        Route::get('/{id}', [PostController::class, 'show']);
        // delete post
        Route::delete('/{id}', [PostController::class, 'destroy']);
        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('/', [PostController::class, 'createPost']);
        });
    });


    Route::group(['prefix'=>'movielist'], function(){
        Route::controller(MovieListController::class)->group(function(){
            Route::get('/allmovielist', 'getallmovielist');
            Route::post('/update/{id}', 'updatemovielist');
            Route::get('/delete/{id}', 'deletemovielist');
        });
    });


    Route::group(['prefix'=>'movie'], function(){
        Route::group(['middleware' => 'auth:api'], function () {
            // get all movies
            Route::get('/allmovies', [MovieController::class,'index']);
             // comedyList
             Route::get('/comedy-list', [MovieController::class,'comedyList']);
            //  get random movie
            Route::get('/random', [MovieController::class,'rando']);
            // create movie
            Route::post('/{movieList:id}', [MovieController::class,'create']);
            // find movie
            Route::get('/{id}', [MovieController::class,'show']); 
           
            // upload to amazon movie
            Route::post('/store', [MovieController::class,'store']);

            // delete movie
            Route::delete('/{id}', [MovieController::class,'destroy']);
            // delete movie
            Route::delete('/delete/{id}', [MovieController::class,'delete']);
        });
    });


    Route::group(['prefix'=>'moviefile'], function(){
        Route::group(['middleware' => 'auth:api'], function () {
           // upload moviefile
           Route::post('/{movie:id}', [MovieFileController::class,'uploadmovie']); 
           Route::get('/all', [MovieFileController::class,'index']); 
           Route::get('/{id}', [MovieFileController::class,'show']); 
           Route::delete('/{id}', [MovieFileController::class,'destroy']); 
           Route::get('/', [MovieFileController::class,'getAllFiles']); 
        });
    });

    Route::group(['prefix'=>'movielist'], function(){
        Route::group(['middleware' => 'auth:api'], function () {
            Route::get('/all', [MovieListController::class,'getAllMovieList']); 
            Route::get('/type', [MovieListController::class,'index']); 
            // upload moviefile
            Route::post('/', [MovieListController::class,'create']); 
            Route::get('/{id}', [MovieListController::class,'show']); 
            Route::delete('/{id}', [MovieListController::class,'deleteMovieList']); 
           
        });
    });

    // Route::group(['prefix' => 'followers'], function () {
    //     Route::get('/', [FollowersController::class, 'getAllFollowers']);
    //     Route::get('/{id}', [FollowersController::class, 'show']);
    // });

    Route::group(['middleware' => 'auth:api'], function () {
          // make payment
          Route::post('/make-payment', [PaymentController::class, 'makePayment'])->name('make-payment');
    });




