<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Mail\MailableCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\Code;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class CodeController extends Controller
{
    /**
     * Save a new code in the database.
     *
     * @return string
     *
     **/
    public function store()
    {
        $code = $this->generateRandomCode();

        Code::create([
            'code' => $code,
            'is_used' => false
        ]);

        return $code;
    }

    /**
     * Random code generated.
     * @return string
     */
    public function generateRandomCode()
    {
        return Str::random(10);
    }

    /**
     * Send a code by email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path="/send-code-by-email",
     *     tags={"User"},
     *     summary="Send a code by email.",
     *     description="Sends a generated code to the specified email address.",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="The email address to send the code to.",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="email"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Email sent successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid email"),
     *             @OA\Property(property="errors", type="object", example={"email": {"The email field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function sendCodeByEmail(Request $request)
    {
        if (Auth::user()->role !== 'ADMIN') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $validEmail = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validEmail->fails()) {
            return response()->json(['status' => false, 'message' => 'Invalid email', 'errors' => $validEmail->errors()], 400);

        }else{
            $emailAddress = $request->input('email');
            Mail::to($emailAddress)->send(new MailableCode($this->store()));
            return response()->json(['status' => true, 'message' => 'Email sent successfully']);
        }
    }
}