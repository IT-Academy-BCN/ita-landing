<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgetRequest;
use App\Http\Requests\ResetRequest;
use App\Mail\ForgetMail;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;




class ForgetController extends Controller
{
/**
 * @OA\Post(
 *   path="/forgetpassword",
 *   tags={"User"},
 *   summary="send email to recovery password",
 *   description="This endpoint is used send an email to a register user to reset the password.",
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="application/json",
 *       @OA\Schema(
 *         @OA\Property(
 *           property="email",
 *           type="string",
 *           example="example@example.com"
 *         ),        
 *       )
 *     )
 *   ),
 *   @OA\Response(
 *     response="200",
 *     description="check your email"
 *   ),
 *   @OA\Response(
 *     response="404",
 *     description="The email don\'t exist"
 *   )
 * )
 */

    public function forgetPassword(ForgetRequest $request){

        $email = $request->email;

        $user= User::where('email',$email)->doesntExist();

        $token= Str::random(10);

        $existingMail = DB::table('password_reset_tokens')->where('email', $email)->first();

        
        try{

            if($user){
                return response()->json(['error' => 'The email don\'t exist'],404);
                
            }else if($existingMail){

                DB::table('password_reset_tokens')->where('email', $email)->update([
                    'token' => $token,
                   ]);

            } else {

                DB::table('password_reset_tokens')->insert([
                    'email' => $email,
                    'token' => $token
                ]);                
            }

            //send email

            Mail::to($email)->send(new ForgetMail($token));
    
                return response()->json(['message'=>'check your email'],200);
            

        }catch(Exception $exception){

            return response()->json(['message' => $exception->getMessage()],404);

        }
 
    }

    /**
 * @OA\Post(
 *   path="/resetPassword/{token}",
 *   tags={"User"},
 *   summary="User recovery password",
 *   description="This endpoint is used to update the password of the user.",
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="application/json",
 *       @OA\Schema(
 *         @OA\Property(
 *           property="token",
 *           type="string",
 *           example="abcdefghij"
 *         ),
 *          @OA\Property(
 *           property="password",
 *           type="string",
 *           example="password"
 *         ),
 *          @OA\Property(
 *           property="password_confirm",
 *           type="string",
 *           example="password"
 *         ),        
 *       )
 *     )
 *   ),
 *   @OA\Response(
 *     response="200",
 *     description="success"
 *   ),
 *   @OA\Response(
 *     response="400",
 *     description="Invalid Token!"
 *   )
 * )
 */

    public function resetPassword(ResetRequest $request){

        $token = $request->token;

        $passwordResets= DB::table('password_reset_tokens')->where('token', $token)->first();

                        
        if(!$passwordResets){

            return response()->json([
                'error' => 'Invalid Token!'
            ],400);
        }
        /** @var User $user */
        $user= User::where('email',$passwordResets->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'success'
        ],200);

    }
}