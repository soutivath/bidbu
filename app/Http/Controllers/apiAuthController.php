<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Firebase\Auth\Token\Exception\InvalidToken;
use Auth;
use Response;
use GuzzleHttp\Client;
class apiAuthController extends Controller
{
    public function __construct()
    {

    }
    public function login(Request $request){

        $request->validate([
            'firebase_token'=>'required|string',
            'phone_number'=>'required|numeric',
            'password'=>'required|string'
        ]);
  $auth = app('firebase.auth');
  $idTokenString = $request->input('firebase_token');
  try { // Try to verify the Firebase credential token with Google
    $verifiedIdToken = $auth->verifyIdToken($idTokenString);
  } catch (\InvalidArgumentException $e) { // If the token has the wrong format
    return response()->json([
        'message' => 'Unauthorized - Can\'t parse the token: ' . $e->getMessage()
    ], 401);        
  } catch (InvalidToken $e) { // If the token is invalid (expired ...)
    return response()->json([
        'message' => 'Unauthorized - Token is invalide: ' . $e->getMessage()
    ], 401);
  }
  $uid = $verifiedIdToken->getClaim('sub');
  if(User::where('firebase_uid',$uid)->first())
  {
    $client = new \GuzzleHttp\Client(
        [
            'timeout'=>15
        ]
    );
    if(!Auth::attempt(['phone_number'=>$request->phone_number,'password'=>$request->password])){
        return response()->json(['message'=>'Unauthorized'],401);
    }
    try{
        $response = $client->post('http://127.0.0.1:8001/oauth/token',[
            'form_params'=>[
                'grant_type' => 'password',
                'client_id' => 2,
                'client_secret' => '2DSpJLVNMdfmY5z0fQjFaWWDcBEMuffIsDF8J8Qe',
                'username'=>$request->phone_number,
                'password'=>$request->password
            ]
        ]);
       return json_decode($response->getBody(),true);
       // return $response->json_decode($response);
    }
    catch(\Guzzle\Exception\BadResponseException $e)
    {
        if($e->getCode===400){
            return response()->json("Invalid request. Please enter a username or a password.".$e->getCode());
        }
        else if($e->getCode===401)
        {
            return response()->json("Your Credentials are incorrect. Please Try Again".$e->getCode());
        }
        return response()->json('Something went Wrong on the Server'.$e->getCode());
    }
  }
  else{
      return response()->json(['message'=>'Can not found the User'],401);
  }
  
    }

    public function logOut()
    {
        auth()->user()->tokens->each(function($token,$key){
            $token->delete();
        });
        return response()->json('Logout Successfully',200);
    }



   
    public function register(Request $request)
    {
        $request->validate([
            'name'=>'required|min:5|max:30|string',
            'surname'=>'required|min:5|max:30|string',
            //'email'=>'required|email',
            'phone_number'=>'required|integer',
            'firebase_token'=>'required|string',
            'password'=>'required|string|min:6|max:18',
            'picture'=>'sometimes|image|mines:jpeg,png,jpg,PNG|max:8192'
        ]);
        $defaultImage = "default_image.jpg";
        if($request->hasFile('picture'))
        {
            $image = $request->file('profile_image');
            $fileExtension = $image->getClientOriginalExtension();
            $fileName = 'profile_image_'.time().'.'.$fileExtension;
            $location = public_path("/profile_image/".$fileName);
            Image::make($image)->resize(460,460,function($c) { $c->aspectRatio(); })->save($location);
            $defaultImage= $fileName;
        }
         $auth = app('firebase.auth');
         $idTokenString = $request->firebase_token;
         try { // Try to verify the Firebase credential token with Google
            $verifiedIdToken = $auth->verifyIdToken($idTokenString);
          } catch (\InvalidArgumentException $e) { // If the token has the wrong format
            return response()->json(
                [
                    'message' => 'Unauthorized - Can\'t parse the token: ' . $e->getMessage()
                ],401
            );
          } catch (InvalidToken $e) { // If the token is invalid (expired ...)
            
            return response()->json([
                'message' => 'Unauthorized - Token is invalide: ' . $e->getMessage()
            ], 401);
          }
         
            // Retrieve the UID (User ID) from the verified Firebase credential's token
        $uid = $verifiedIdToken->getClaim('sub');
        $user = new User([
            'name'=>$request->name,
            'surname'=>$request->surname,
          //  'email'=>$request->email,
            'phone_number'=>$request->phone_number,
           // 'mobile'=>$request->mobile,
            'firebase_uid'=>$uid,
            'password'=>bcrypt($request->password),
            'picture'=>$defaultImage
        ]);
        
        if($user->save())
        {
                $user->attachRole("bond");
              $http = new \GuzzleHttp\Client([
                'timeout' => 60
            ]);
            try {
                $response = $http->post('http://127.0.0.1:8001/oauth/token', [
                    'form_params' => [
                        'grant_type' => 'password',
                        'client_id' => 2,
                        'client_secret' => '2DSpJLVNMdfmY5z0fQjFaWWDcBEMuffIsDF8J8Qe',
                        'username' => $request->phone_number,
                        'password' => $request->password,
                    ]
                ]);
                return $response->getBody();
            } catch (\GuzzleHttp\Exception\BadResponseException $e) {
                if ($e->getCode() === 400) {
                    return response()->json('Invalid Request. Please enter a Phone number or a password.', $e->getCode());
                } else if ($e->getCode() === 401) {
                    return response()->json('Your credentials are incorrect. Please try again', $e->getCode());
                }
      
                return response()->json('Something went wrong on the server.', $e->getCode());
            }
        }
        else{
            return response()->json(['message'=>'Something went Wrong'],500);
        }
        
        
    }


    public function altLogin(Request $request)
    {
       // return \response()->json(['message'=>'mice'],201);
      $http = new \GuzzleHttp\Client([
          'timeout' => 15
      ]);
      try {
          $response = $http->post('http://127.0.0.1:8001/oauth/token', [
              'form_params' => [
                  'grant_type' => 'password',
                  'client_id' => 2,
                  'client_secret' => 'P7tr4DCgtufwq3AHweJMxfjsoxaw1TZi5atZGYUN',
                  'username' => $request->phone_number,
                  'password' => $request->password,
              ]
          ]);
          return $response->getBody();
      } catch (\GuzzleHttp\Exception\BadResponseException $e) {
          if ($e->getCode() === 400) {
              return response()->json('Invalid Request. Please enter a Phone number or a password.', $e->getCode());
          } else if ($e->getCode() === 401) {
              return response()->json('Your credentials are incorrect. Please try again', $e->getCode());
          }

          return response()->json('Something went wrong on the server.', $e->getCode());
      }

    }


   /* public function login(Request $request){
        $request->validate([
            'email'=>'string|email|required',
            'password'=>'string|min:6|max:18|required',
        ]);
        $credentials = request('email','password');
        if(!Auth::attempt($credentials)){
            return response()->json(['message'=>'Unauthorized'],401);
        }
        $user = $request->user();
        $tokenResult = $user->createToken("Personal Access Token");
        $token = $tokenResult->token;
        $token->save();
        return response()->json([
            'access_token'=>$tokenResult->accessToken,
            'token_type'=>'Bearer',
            'expires_at'=>Carbon::parse($tokenResult->token->expires_at)->toDateTimeString()
        ]);*/

    //}

 /*   public function login(Request $request) {
  
        // Launch Firebase Auth
        $auth = app('firebase.auth');
        // Retrieve the Firebase credential's token
        $idTokenString = $request->input('Firebasetoken');
      
        
        try { // Try to verify the Firebase credential token with Google
          
          $verifiedIdToken = $auth->verifyIdToken($idTokenString);
          
        } catch (\InvalidArgumentException $e) { // If the token has the wrong format
          
          return response()->json([
              'message' => 'Unauthorized - Can\'t parse the token: ' . $e->getMessage()
          ], 401);        
          
        } catch (InvalidToken $e) { // If the token is invalid (expired ...)
          
          return response()->json([
              'message' => 'Unauthorized - Token is invalide: ' . $e->getMessage()
          ], 401);
          
        }
      
        // Retrieve the UID (User ID) from the verified Firebase credential's token
        $uid = $verifiedIdToken->getClaim('sub');
      
        // Retrieve the user model linked with the Firebase UID
        $user = User::where('firebaseUID',$uid)->first();
        
        // Here you could check if the user model exist and if not create it
        // For simplicity we will ignore this step
      
        // Once we got a valid user model
        // Create a Personnal Access Token
        $tokenResult = $user->createToken('Personal Access Token');
        
        // Store the created token
        $token = $tokenResult->token;
        
        // Add a expiration date to the token
        $token->expires_at = Carbon::now()->addWeeks(1);
        
        // Save the token to the user
        $token->save();
        
        // Return a JSON object containing the token datas
        // You may format this object to suit your needs
        return response()->json([
          'id' => $user->id,
          'access_token' => $tokenResult->accessToken,
          'token_type' => 'Bearer',
          'expires_at' => Carbon::parse(
            $tokenResult->token->expires_at
          )->toDateTimeString()
        ]);
      
      }*/
    


}
