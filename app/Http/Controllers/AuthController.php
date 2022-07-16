<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private function return_error($error, $ref)
    {
        return response([
            'status' => 'error',
            'ref' => $ref,
            'message' => $error
        ], 201);
    }

    private function create_chat($uid_1, $uid_2)
    {
        $chat_check = Chat::where('owner_id', $uid_1)
                        ->where('chat_with_id', $uid_2)->first();
        
        if (!$chat_check){
            $chat = Chat::create([
                'type' => 'one-to-one',
                'owner_id' => $uid_1,
                'chat_with_id' => $uid_2
            ]);
        }
    }

    private function create_chats($user)
    {
        $community_leaders = User::where('access', 'community_leader')->get();

        foreach($community_leaders as $community_leader){
            $this->create_chat($community_leader->id, $user->id);
        }

        $admins = User::where('access', 'admin')->get();
        
        foreach($admins as $admin){
            $this->create_chat($admin->id, $user->id);
        }
    }

    public function create(Request $request)
    {
        $creds = $request->only(['email', 'password', 'name']);

        $user = User::where('email', $creds['email'])->first();

        if ($user){
            return $this->return_error('email already taken', 'ERR 001');
        }

        $user = User::create([
            'name' => $creds['name'],
            'email' => $creds['email'],
            'access' => 'user', // admin
            'password' => bcrypt($creds['password'])
        ]);

        $this->create_chats($user);

        return response([
            'status' => 'success',
            'message' => 'user created successfully please login'
        ], 201);
    }

    public function login(Request $request)
    {
        $creds = $request->only(['email', 'password']);

        $user = User::where('email', $creds['email'])->first();

        if (!$user){
            return $this->return_error('email not found', 'ERR 002');
        }

        if (!Hash::check($creds['password'], $user->password) || $creds['password'] == ""){
            return $this->return_error('wrong password', 'ERR 003');
        }

        $token = $user->createToken('createToken')->plainTextToken;

        $user->push($user->user_media);

        return response([
            'status' => 'success',
            'message' => 'logged in successfully',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response([
            'status' => 'success',
            'message' => 'successfully logged out'
        ], 201);
    }
}
