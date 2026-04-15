<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CreatorController extends Controller
{
    //
       public function Register(Request $request)
    {


        try {
            $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone'=>  'required|string|max:10',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'creator',
        ]);}
        catch (\Exception $e) {
            \Log::info('Registration failed: ' . $e->getMessage());
            return response()->json(['error' => 'Registration failed', 'message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Registration successful'], 201);
    }
    public function Login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        try {
            $user = User::where('username', $request->username)->first();


        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        if($user->role !== 'creator') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        } catch (\Exception $e) {
            \Log::info('Login failed: ' . $e->getMessage());
            return response()->json(['error' => 'Login failed', 'message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Login successful', 'token' => $token]);
    }
    public function Logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
        } catch (\Exception $e) {
            \Log::info('Logout failed: ' . $e->getMessage());
            return response()->json(['error' => 'Logout failed', 'message' => $e->getMessage()], 500);
        }
        return response()->json(['message' => 'Logout successful']);
    }
}
