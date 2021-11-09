<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Telegram\Bot\Api;

class AuthenticationController extends Controller
{

    public function register(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Error Validation', $validator->errors(), 400);
        }

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => bcrypt($input['password']),
            'role_id' => 2,
        ]);
        $token = $user->createToken('token')->plainTextToken;

        $telegramMessage = "Pengguna baru yang terdaftar pada web, nama pengguna: {$input['name']}";

        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

        $telegram->sendMessage([
            'chat_id' => env('TELEGRAM_CHAT_GROUP_ID_SAMPLE'),
            'text' => $telegramMessage
        ]);

        $data = [
            'user' => $user,
            'token' => $token,
        ];

        return $this->responseSuccess('Registration Successful', $data, 201);
    }
    //use this method to login users
    public function login(Request $request)
    {
        $input = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|between:8,255'
        ]);

        if (!Auth::attempt($input)) {
            return $this->responseFailed('Email or Password is incorrect', '', 401);
        }

        $user = User::where('email', $input['email'])->first();
        $token = $user->createToken('token')->plainTextToken;

        $data = [
            'user' => $user,
            'token' => $token
        ];

        $telegramMessage = "Pengguna sedang login di web, email: {$input['email']}";

        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

        $telegram->sendMessage([
            'chat_id' => env('TELEGRAM_CHAT_GROUP_ID_SAMPLE'),
            'text' => $telegramMessage
        ]);

        return $this->responseSuccess('Login Successful', $data, 200);
    }

    // this method signs out users by removing tokens
    public function logout()
    {
        $user = auth()->user()->email;

        $telegramMessage = "Pengguna telah logout dari web, email: {$user}";

        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

        $telegram->sendMessage([
            'chat_id' => env('TELEGRAM_CHAT_GROUP_ID_SAMPLE'),
            'text' => $telegramMessage
        ]);

        auth()->user()->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logout Successful'
        ]);
    }
}
