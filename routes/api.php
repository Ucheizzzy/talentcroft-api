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
        Route::group(['middleware' => 'auth:api'], function () {
            // get authenticated user
            Route::get('/profile/{id}', [UserController::class, 'getAuthenticatedUser']);
            // update user credentials
            Route::put('/update/{id}', [UserController::class, 'updateUser']);
            // delete user
            Route::delete('/{id}', [UserController::class, 'deleteUser']);
        });
        //get all users
        Route::get('/allusers', [UserController::class,'getAllUsers']);
        //get a user
        Route::get('/{id}', [UserController::class,'getUser']);
        // forgetpassword
        Route::post('/forgetpassword', [UserController::class, 'forgetpassword']);
        // reset password
         Route::post('/resetpassword', [UserController::class, 'resetpassword']);


        //  create account details
        Route::post('/create/account', [UserController::class, 'createAccount']);

         //  get account
        Route::get('/getaccount/{id}', [UserController::class, 'getAccountDetails']);

         //  delete account
        Route::get('/deleteaccount/{id}', [UserController::class, 'deleteAccountDetails']);

         //Get Movie
        Route::get('getamovie', [ UserController::class, 'getMovie' ]);

        //Delete a movie
        Route::delete('/{id}', [UserController::class,'deleteMovie']);

        //Getting a movie at random
        Route::get('/getrandommovie', [UserController::class,'randomMovie']);

        //creating a post
        Route::post('/createpost', [UserController::class,'createPost']);

        //Getting a post
        Route::get('/getpost', [UserController::class,'getPost']);

        //updating a post
        Route::post('/updatepost/{id}', [UserController::class,'updatePost']);



    });
   
    Route::group(['middleware' => ['api', 'cors'], 'prefix' => 'post'], function () {
         // find all post
        Route::get('/allpost', [PostController::class, 'getAllPost']);
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
            // create movie
            Route::post('/create', [MovieController::class,'create']);
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
           Route::get('/{id}', [MovieFileController::class,'show']); 
           Route::delete('/{id}', [MovieFileController::class,'destroy']); 
           Route::get('/', [MovieFileController::class,'getAllFiles']); 
        });
    });

    Route::group(['middleware' => 'auth:api'], function () {
          // make payment
          Route::post('/make-payment', [PaymentController::class, 'makePayment'])->name('make-payment');
    });




