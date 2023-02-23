<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;

class FollowerController extends Controller
{
    public function createFollow(Request $request){

        $request->validate([
            'followedUser'=>'required'
        ]);
        $user = auth()->user();
        $follow = Follow::where('followedUser', $request->followedUser)->where('user_id', $user->id)->first();

        //you cannot follow yourself
        if($request->followedUser == $user->id){
            return response()->json([
                'message'=>'Ooops you cannot follow yourself'
            ], 500);
        }
        
        if(!$follow){
            $follow = new Follow();
            $follow->followedUser = $request->followedUser;
            $follow->user_id = $user->id;

            if($follow->save()){
                return response()->json([
                    'message'=> "$user->first_name you are now following this user"
                ], 200);
            }else{
                return response()->json([
                    'message'=> 'Oops something went wrong following this user, try again'
                ], 500);
            }

        }else{
            if($follow->delete()){
                return response()->json([
                    'message'=> "$user->first_name unfollowed this user"
                ], 200);
            }else{
                return response()->json([
                    'message'=>'Something went wrong unfollowing this user.'
                ], 500);
            }
        }
     }
}