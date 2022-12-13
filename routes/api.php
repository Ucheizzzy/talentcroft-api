<?php
header('Accept: application/json', true);

use App\Http\Controllers\{
    UserController,
    PaymentController,
    ProjectController,
    SettingsController,
    ApiController,
    MovieListController,
    MovieController,
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

        //  delete a post
        Route::get('/deletepost/{id}', [UserController::class, 'deletePost']);

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
   
    Route::group(['prefix' => 'post'], function () {
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
        Route::post('/upload', 'uploadmovie');
        Route::get('/destroy/{id}', 'destroy');
    });


    Route::group(['middleware' => 'auth:api'], function () {
          // make payment
          Route::post('/make-payment', [PaymentController::class, 'makePayment'])->name('make-payment');
    });




