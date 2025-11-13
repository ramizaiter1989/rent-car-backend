<?php

// ============================================================
// FILE: app/Http/Controllers/Api/AuthController.php
// ============================================================
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Client;
use App\Models\Agent;
use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $code = rand(100000, 999999);
        
        Otp::create([
            'phone_number' => $request->phone_number,
            'code' => $code,
            'expired_at' => Carbon::now()->addMinutes(5),
        ]);

        return response()->json([
            'message' => 'OTP sent successfully',
            'code' => $code,
        ], 200);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:100|unique:users',
            'phone_number' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:6',
            'otp_code' => 'required|string|size:6',
            'role' => 'required|in:agency,client,employee,admin,company,advertiser,third_party',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'license_number' => 'required_if:role,client',
            'business_type' => 'required_if:role,agency',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $otp = Otp::where('phone_number', $request->phone_number)
            ->where('code', $request->otp_code)
            ->where('expired_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$otp) {
            return response()->json(['error' => 'Invalid or expired OTP'], 400);
        }

        $user = User::create([
            'username' => $request->username,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'otp_verification' => true,
        ]);

        if ($request->role === 'client') {
            Client::create([
                'user_id' => $user->id,
                'license_number' => $request->license_number,
            ]);
        } elseif ($request->role === 'agency') {
            Agent::create([
                'user_id' => $user->id,
                'business_type' => $request->business_type,
            ]);
        }

        $otp->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'user' => $user->load(['client', 'agent']),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user->load(['client', 'agent']),
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    public function profile(Request $request)
    {
        return response()->json(['user' => $request->user()->load(['client', 'agent'])], 200);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $user->update($request->only(['first_name', 'last_name', 'bio', 'profile_picture']));
        return response()->json(['message' => 'Profile updated', 'user' => $user], 200);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Current password incorrect'], 400);
        }

        $user->update(['password' => Hash::make($request->new_password)]);
        return response()->json(['message' => 'Password changed'], 200);
    }

    public function deleteRequest(Request $request)
    {
        $request->user()->update(['status' => false]);
        return response()->json(['message' => 'Deletion requested'], 200);
    }

    public function deleteAccount(Request $request)
    {
        $request->user()->delete();
        return response()->json(['message' => 'Account deleted'], 200);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|exists:users',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $code = rand(100000, 999999);
        Otp::create([
            'phone_number' => $request->phone_number,
            'code' => $code,
            'expired_at' => Carbon::now()->addMinutes(5),
        ]);

        return response()->json(['message' => 'OTP sent', 'code' => $code], 200);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required',
            'otp_code' => 'required|size:6',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $otp = Otp::where('phone_number', $request->phone_number)
            ->where('code', $request->otp_code)
            ->where('expired_at', '>', Carbon::now())
            ->first();

        if (!$otp) {
            return response()->json(['error' => 'Invalid OTP'], 400);
        }

        $user = User::where('phone_number', $request->phone_number)->first();
        $user->update(['password' => Hash::make($request->new_password)]);
        $otp->delete();

        return response()->json(['message' => 'Password reset'], 200);
    }
}
