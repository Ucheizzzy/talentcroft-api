<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\MovieFile;
use Exception;
use App\Models\Post;
use App\Models\User;
use http\Env\Response;
use App\Mail\ForgetMail;
use Illuminate\Http\Request;
use App\Models\AccountDetails;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ResetRequest;
use App\Http\Requests\ForgetRequest;
use App\Http\Resources\UserResource;
use App\Models\Followers;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;


class UserController extends Controller
{

    private $repo;
    private string $api_key = 'VIEWS_API';


    public function __construct(UserRepository $repo)
    {
        $this->repo = $repo;
    }

    final public function login(Request $request): JsonResponse
    {
        $this->validateData(['email' => 'required|email', 'password' => 'required']);

        $user = User::query()->where('email', $request->email)->first();

        if (!$user)
            return $this->respondWithError('Account not found');

        if (!Hash::check($request->password, $user->password))
            return $this->respondWithError(['message' => 'Invalid credentials']);

        $token = $user->createToken($this->api_key)->accessToken;
        return $this->respondWithSuccess(['data' => ['token' => $token, 'user' => $this->prepareUserData($user)]], 201);
    }

    /**
     * @param User $user
     * @return User
     */
    final public function prepareUserData(User $user): User
    {
        return $this->repo->prepareUserData($user);
    }


    /**
     * Gets Authenticated User
     * @param Request $request
     * @return array
     */

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    final public function register(Request $request): JsonResponse
    {
        $request->validate(['first_name' => 'required|min:2', 'last_name' => 'required|min:2',
            'email' => 'required|email|unique:users', 'phone_number' => 'required|unique:users,phone_number',
            'password' => 'required'
        ]);

        $user = DB::transaction(function () use ($request) {
            $data = $request->all();
            $data['password'] = Hash::make($request->password);
            $data['avatar'] = textToImage(text: 'No avatar', bg: randomColorCode());
            $user = User::create($data);
            return $user;
        });
        $accessToken = $user->createToken($this->api_key)->accessToken;
        return $this->respondWithSuccess(['data' => ['token' => $accessToken, 'user' => $this->repo->prepareUserData($user)]], 201);
    }

    final public function createUser(Request $request): JsonResponse
    {
        $request->validate(['first_name' => 'required|min:2', 'last_name' => 'required|min:2',
            'email' => 'required|email|unique:users',
            'password' => 'required', 'user_type' => 'required'
        ]);

        $user = DB::transaction(function () use ($request) {
            $data = $request->all();
            $data['password'] = Hash::make($request->password);
            $data['avatar'] = textToImage(text: 'No avatar', bg: randomColorCode());
            $user = User::create($data);
            return $user;
        });
        $accessToken = $user->createToken($this->api_key)->accessToken;
        return $this->respondWithSuccess(['data' => ['token' => $accessToken, 'user' => $this->repo->prepareUserData($user)]], 201);
    }

    // final public function getAuthenticatedUser(Request $request, $id): JsonResponse
    // {
    //     $user = User::findOrFail($id);
    //     $authenticated = getUser()->id;
    //     if ($user->id == $authenticated) return $this->respondWithSuccess(['data'=>['message' =>'Authenticated User', 'user' => $this->repo->prepareUserData($user)]], 201);
    //     return $this->respondWithError(['data'=>['message' =>'Account not found', 'user' => []]], 404);
    // }

    final public function getAuthenticatedUser(Request $request): JsonResponse
    {
        if (!$user = getUser()) return $this->respondWithError('Account not found', 404);
        // (new UserRepository($user))->onBoard();
        return $this->respondWithSuccess($this->prepareUserData($user));
        // TODO::
    }

    public function getUser($id)
    {
        $user = User::findOrFail($id);
        return $this->respondWithSuccess(['data' => ['user' => $this->prepareUserData($user)]], 201);
    }


    final public function deleteUser($id)
    {
        // if (auth()->user()->user_type = 'admin'){
            $user = User::findOrFail($id);
            $user->delete();
            return $this->respondWithSuccess(['data' => ['message' => 'user '.$id.' has been deleted' ,'pricing' => $user]], 201);
        // } else {
            // return$this->respondWithSuccess(['message' => 'Only Admin can delete User'], 201);
        // }
    }

    // public function updateUser(Request $request, $id){
    //     $request->validate([
    //         'first_name' => 'nullable|min:2',
    //         'last_name' => 'nullable|min:2',
    //         'phone_number' => 'nullable|unique:users,phone_number',
            
    //     ]);
     
    //     try{
    //         $user = User::findorfail($id)->update([
    //             'first_name'=>$request->first_name,
    //             'last_name'=>$request->last_name,
    //             'phone_number'=>$request->phone_number,
    //             'avatar'=>$request->avatar,
    //             'location'=>$request->location,
    //             'bio'=>$request->bio,
    //             'tags'=>$request->tags,
    //             'banner'=>$request->banner,
    //             'password'=>Hash::make($request->password),
    //         ]);
    //         // generate Random Token min of 10 and  characters
    //          $token = rand(10,100000);
    //         return response([
    //             'message'=>'user updated successful',
    //             'token'=>$token,
    //             'user'=>$user,
    //         ], 200);
    //     }catch(Exception $exception){
    //         return response([
    //             'message'=>$exception->getMessage(),
    //         ], 400);
    //     } 
    // }


    public function updateUser(Request $request, $id)
    {
        $user = User::find($id);
        $user->update($request->all());
        return response([ 'message'=>'user updated successful', 'user'=>$user], 201);

    }

    // public function updateUser(Request $request, $id){
    //     $request->validate([
    //         'first_name' => 'nullable|min:2',
    //         'last_name' => 'nullable|min:2',
    //         'phone_number' => 'nullable|unique:users,phone_number',
            
    //     ]);
     
    //     try{
    //         $user = User::findorfail($id)->update([
    //             'first_name'=>$request->first_name,
    //             'last_name'=>$request->last_name,
    //             'phone_number'=>$request->phone_number,
    //             'avatar'=>$request->avatar,
    //             'location'=>$request->location,
    //             'bio'=>$request->bio,
    //             'tags'=>$request->tags,
    //             'banner'=>$request->banner,
    //             'password'=>Hash::make($request->password),
    //         ]);
    //         // generate Random Token min of 10 and  characters
    //          $token = rand(10,100000);
    //         return response([
    //             'message'=>'user updated successful',
    //             'token'=>$token,
    //             'user'=>$user,
    //         ], 200);
    //     }catch(Exception $exception){
    //         return response([
    //             'message'=>$exception->getMessage(),
    //         ], 400);
    //     } 
    // }

    public function getAllUsers(){
        $users = User::latest()->get();
        $data = UserResource::collection($users);
        return $this->respondWithSuccess(['data' => ['message' => 'All talencroft users', 'users' => $data]], 201);
    }


    public function forgetpassword(ForgetRequest $request){
           $email = $request->email;
        //    check if the email exists in the database
        if(User::where('email', $email)->doesntExist()){
            return response([
                'message' =>'Email invalid',
            ], 401);
        }
            // generate Randome Token min of 10 and  characters
        $token = rand(10,100000);

        try{
            DB::table('password_resets')->insert([
                'email'=>$email,
                'token'=>$token,
            ]);
            // sending mail to user
            Mail::to($email)->send(new ForgetMail($token));
            return response([
                'message'=> 'Reset password Email sent to your mail'
            ], 200);

        }catch(Exception $exception){
            return response([
                'message'=>$exception->getMessage(),
            ], 400);

        }

    }

    final public function getFollowers()
    {
        $follower = Followers::latest()->get();
        return $this->respondWithSuccess(['data' => ['message' => 'All followers', 'follower' => $follower]], 201);
    }

    final public function followUser(Followers $follower, User $user)
    {
        $authId = auth()->id();
        if (!$authId) {
            return $this->respondWithError('Authentication error: user ID is null');
        }
    
        //Delete if exist
        $follow = $follower->where(['follower_id' => $authId])->first();
        if ($follow) {
            $follow->delete();
            return $this->respondWithSuccess('you just unfollowed' . ' ' . $user->first_name);
        }
    
        $followed = $follower->updateOrCreate(['follower_id' => $authId], ['user_id' => $user->id]);
        return $this->respondWithSuccess('you just followed' . ' ' . $user->first_name);
    }
    

    // final public function followUser(Followers $followers, User $user)
    // {
    //     // dd($user->id, $user->follower());
    //     //Delete if exist
    //     $follow = $followers->where(['follower_id' => auth()->id()])->first();
    //     if ($follow) {
    //         $follow->delete();
    //         return $this->respondWithSuccess('delete');
    //     }
    //     $followed = $followers->updateOrCreate(['follower_id' => auth()->id()], ['user_id' => $user->id]);
    //     return $this->respondWithSuccess('followed');
    // }  

    public function resetpassword(ResetRequest $request){

        $email = $request->email;
        $token = $request->token;
        $password = Hash::make($request->password);

        // get email and token from the database
        $emailcheck = DB::table('password_resets')->where('email',$email)->first();
        $pincheck = DB::table('password_resets')->where('token',$token)->first();

        // validate and check if they exits
        if (!$emailcheck) {
            return response([
                'message' => "Email Not Found"
            ],401);          
         }
         if (!$pincheck) {
            return response([
                'message' => "Pin Code Invalid"
            ],401);          
         }
        //  update the password
         DB::table('users')->where('email',$email)->update(['password' => $password]);
         DB::table('password_resets')->where('email',$email)->delete();

         return response([
            'message' => 'Password Change Successfully'
         ],200);
    
    }
}
