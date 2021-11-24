<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Telegram\Bot\Api;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = User::with(['role' => function ($q) {
            $q->select('id', 'role_name');
        }])->get();
        return $this->responseSuccess('List all users', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|between:8,255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Error Validation', $validator->errors(), 400);
        }

        if ($request->hasFile('avatar')) {
            $input['avatar'] = rand() . '.' . request()->avatar->getClientOriginalExtension();
            request()->avatar->move(public_path('assets/images/user/avatar/'), $input['avatar']);
        }

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'avatar' => $input['avatar'],
            'password' => bcrypt($input['password']),
            'role_id' => 2,
        ]);

        $telegramMessage = "Pengguna baru berhasil ditambahkan, nama pengguna: {$input['name']}";

        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

        $telegram->sendMessage([
            'chat_id' => env('TELEGRAM_CHAT_GROUP_ID_SAMPLE'),
            'text' => $telegramMessage
        ]);

        $data = User::where('id', $user->id)->with(['role' => function ($q) {
            $q->select('id', 'role_name');
        }])->first();

        return $this->responseSuccess('User created Successfully', $data, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::where('id', $id)->first();
        if (!$user) return $this->responseFailed('Data not found', '', 404);

        $data = User::where('id', $id)->with(['role' => function ($q) {
            $q->select('id', 'role_name');
        }])->first();
        return $this->responseSuccess('User detail', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $user = User::where('id', $id)->first();
        if (!$user) return $this->responseFailed('Data not found', '', 404);

        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required|string',
            'email' => 'required|string|email',
            'role_id' => 'required|numeric',
            'avatar' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Error validation', $validator->errors(), 400);
        }

        $oldAvatar = $user->avatar;
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = rand() . '.' . request()->avatar->getClientOriginalExtension();
            $input['avatar'] = $avatarName;
            $avatar->move(public_path('assets/images/user/avatar/'), $avatarName);
        } else {
            $input['avatar'] = $oldAvatar;
        }

        $user->update([
            'name' => $input['name'],
            'email' => $input['email'],
            'avatar' => $input['avatar'],
            'role_id' => $input['role_id'],
        ]);

        $telegramMessage = "Pengguna berhasil diubah, nama pengguna: {$input['name']}";

        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

        $telegram->sendMessage([
            'chat_id' => env('TELEGRAM_CHAT_GROUP_ID_SAMPLE'),
            'text' => $telegramMessage
        ]);

        $data = User::where('id', $id)->with(['role' => function ($q) {
            $q->select('id', 'role_name');
        }])->first();

        return $this->responseSuccess('User updated successfully', $data, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::where('id', $id)->first();
        if (!$user) return $this->responseFailed('User not found', '', 404);

        $telegramMessage = "Pengguna berhasil dihapus, nama pengguna: {$user->name}";

        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

        $telegram->sendMessage([
            'chat_id' => env('TELEGRAM_CHAT_GROUP_ID_SAMPLE'),
            'text' => $telegramMessage
        ]);

        $user->delete();

        return $this->responseSuccess('User deleted successfully');
    }
}
