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

    //profile completition function

    ///////////////////////////////////////////////////////////////

    public function checkProfileStatus(Request $request)
{
    $user = $request->user()->load(['client', 'agent']);
    
    $isComplete = $this->isProfileComplete($user);
    $missingFields = $isComplete ? [] : $this->getMissingFields($user);
    $completionPercentage = $this->calculateCompletionPercentage($user);

    return response()->json([
        'is_complete' => $isComplete,
        'completion_percentage' => $completionPercentage,
        'missing_fields' => $missingFields,
        'user' => $user
    ], 200);
}

/**
 * Complete user profile
 */
public function completeProfile(Request $request)
{
    $user = $request->user();
    
    // Base validation rules
    $rules = [
        'first_name' => 'required|string|max:100',
        'last_name' => 'required|string|max:100',
        'gender' => 'required|in:male,female,other',
        'birth_date' => 'required|date|before:today',
        'city' => 'required|in:beirut,tripoli,sidon,tyre,other',
        'id_card_front' => 'required|string',
        'id_card_back' => 'required|string',
        'bio' => 'nullable|string',
        'profile_picture' => 'nullable|string',
    ];

    // Role-specific validation
    if ($user->role === 'client') {
        $rules['license_number'] = 'required|string|max:100';
        $rules['driver_license'] = 'required|string';
        $rules['profession'] = 'required|in:employee,freelancer,business,student,other';
        $rules['avg_salary'] = 'required|in:200-500,500-1000,1000-2000,2000+';
        $rules['promo_code'] = 'nullable|string|max:50';
    } elseif ($user->role === 'agency') {
        $rules['business_type'] = 'required|in:rental,dealer,private,company';
        $rules['profession'] = 'required|in:manager,agent,driver,other';
        $rules['location'] = 'required|json';
        $rules['company_number'] = 'required_if:business_type,company|string|max:100';
        $rules['business_doc'] = 'required_if:business_type,company|json';
        $rules['app_fees'] = 'nullable|numeric|between:0,99.99';
        $rules['contract_form'] = 'nullable|string';
        $rules['policies'] = 'nullable|string';
        $rules['website'] = 'nullable|url';
    }

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Update user base fields
    $user->update($request->only([
        'first_name', 'last_name', 'gender', 'birth_date', 
        'city', 'id_card_front', 'id_card_back', 'bio', 'profile_picture'
    ]));

    // Update role-specific profile
    if ($user->role === 'client') {
        $user->client->update($request->only([
            'license_number', 'driver_license', 'profession', 
            'avg_salary', 'promo_code'
        ]));
    } elseif ($user->role === 'agency') {
        $user->agent->update($request->only([
            'business_type', 'profession', 'location', 'company_number',
            'business_doc', 'app_fees', 'contract_form', 'policies', 'website'
        ]));
    }

    return response()->json([
        'message' => 'Profile completed successfully',
        'user' => $user->fresh()->load(['client', 'agent'])
    ], 200);
}

/**
 * Update partial profile (for step-by-step completion)
 */
public function updatePartialProfile(Request $request)
{
    $user = $request->user();
    
    $validator = Validator::make($request->all(), [
        'first_name' => 'sometimes|string|max:100',
        'last_name' => 'sometimes|string|max:100',
        'gender' => 'sometimes|in:male,female,other',
        'birth_date' => 'sometimes|date|before:today',
        'city' => 'sometimes|in:beirut,tripoli,sidon,tyre,other',
        'id_card_front' => 'sometimes|string',
        'id_card_back' => 'sometimes|string',
        'bio' => 'nullable|string',
        'profile_picture' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Update only provided fields
    $userFields = array_intersect_key(
        $request->all(),
        array_flip(['first_name', 'last_name', 'gender', 'birth_date', 
                    'city', 'id_card_front', 'id_card_back', 'bio', 'profile_picture'])
    );
    
    if (!empty($userFields)) {
        $user->update($userFields);
    }

    // Update role-specific fields if provided
    if ($user->role === 'client' && $user->client) {
        $clientFields = array_intersect_key(
            $request->all(),
            array_flip(['license_number', 'driver_license', 'profession', 'avg_salary', 'promo_code'])
        );
        
        if (!empty($clientFields)) {
            $user->client->update($clientFields);
        }
    } elseif ($user->role === 'agency' && $user->agent) {
        $agentFields = array_intersect_key(
            $request->all(),
            array_flip(['business_type', 'profession', 'location', 'company_number',
                       'business_doc', 'app_fees', 'contract_form', 'policies', 'website'])
        );
        
        if (!empty($agentFields)) {
            $user->agent->update($agentFields);
        }
    }

    $completionStatus = $this->checkProfileStatusInternal($user->fresh());

    return response()->json([
        'message' => 'Profile updated successfully',
        'user' => $user->fresh()->load(['client', 'agent']),
        'completion_status' => $completionStatus
    ], 200);
}

/**
 * Helper: Check if profile is complete
 */
private function isProfileComplete($user): bool
{
    $baseComplete = !empty($user->first_name) &&
                   !empty($user->last_name) &&
                   !empty($user->gender) &&
                   !empty($user->birth_date) &&
                   !empty($user->city) &&
                   !empty($user->id_card_front) &&
                   !empty($user->id_card_back);

    if (!$baseComplete) {
        return false;
    }

    if ($user->role === 'client') {
        $client = $user->client;
        return $client && 
               !empty($client->license_number) &&
               !empty($client->driver_license) &&
               !empty($client->profession) &&
               !empty($client->avg_salary);
    } elseif ($user->role === 'agency') {
        $agent = $user->agent;
        if (!$agent) return false;
        
        $baseAgentComplete = !empty($agent->business_type) &&
                            !empty($agent->profession) &&
                            !empty($agent->location);
        
        if ($agent->business_type === 'company') {
            return $baseAgentComplete && 
                   !empty($agent->company_number) &&
                   !empty($agent->business_doc);
        }
        
        return $baseAgentComplete;
    }

    return true;
}

/**
 * Helper: Get missing fields
 */
private function getMissingFields($user): array
{
    $missing = [];

    if (empty($user->first_name)) $missing[] = 'first_name';
    if (empty($user->last_name)) $missing[] = 'last_name';
    if (empty($user->gender)) $missing[] = 'gender';
    if (empty($user->birth_date)) $missing[] = 'birth_date';
    if (empty($user->city)) $missing[] = 'city';
    if (empty($user->id_card_front)) $missing[] = 'id_card_front';
    if (empty($user->id_card_back)) $missing[] = 'id_card_back';

    if ($user->role === 'client') {
        $client = $user->client;
        if (!$client || empty($client->license_number)) $missing[] = 'license_number';
        if (!$client || empty($client->driver_license)) $missing[] = 'driver_license';
        if (!$client || empty($client->profession)) $missing[] = 'profession';
        if (!$client || empty($client->avg_salary)) $missing[] = 'avg_salary';
    } elseif ($user->role === 'agency') {
        $agent = $user->agent;
        if (!$agent || empty($agent->business_type)) $missing[] = 'business_type';
        if (!$agent || empty($agent->profession)) $missing[] = 'profession';
        if (!$agent || empty($agent->location)) $missing[] = 'location';
        
        if ($agent && $agent->business_type === 'company') {
            if (empty($agent->company_number)) $missing[] = 'company_number';
            if (empty($agent->business_doc)) $missing[] = 'business_doc';
        }
    }

    return $missing;
}

/**
 * Helper: Calculate completion percentage
 */
private function calculateCompletionPercentage($user): int
{
    $totalFields = 7; // Base required fields
    $completedFields = 0;

    if (!empty($user->first_name)) $completedFields++;
    if (!empty($user->last_name)) $completedFields++;
    if (!empty($user->gender)) $completedFields++;
    if (!empty($user->birth_date)) $completedFields++;
    if (!empty($user->city)) $completedFields++;
    if (!empty($user->id_card_front)) $completedFields++;
    if (!empty($user->id_card_back)) $completedFields++;

    if ($user->role === 'client') {
        $totalFields += 4;
        $client = $user->client;
        if ($client) {
            if (!empty($client->license_number)) $completedFields++;
            if (!empty($client->driver_license)) $completedFields++;
            if (!empty($client->profession)) $completedFields++;
            if (!empty($client->avg_salary)) $completedFields++;
        }
    } elseif ($user->role === 'agency') {
        $totalFields += 3;
        $agent = $user->agent;
        if ($agent) {
            if (!empty($agent->business_type)) $completedFields++;
            if (!empty($agent->profession)) $completedFields++;
            if (!empty($agent->location)) $completedFields++;
            
            if ($agent->business_type === 'company') {
                $totalFields += 2;
                if (!empty($agent->company_number)) $completedFields++;
                if (!empty($agent->business_doc)) $completedFields++;
            }
        }
    }

    return $totalFields > 0 ? round(($completedFields / $totalFields) * 100) : 0;
}

/**
 * Helper: Internal status check
 */
private function checkProfileStatusInternal($user)
{
    return [
        'is_complete' => $this->isProfileComplete($user),
        'completion_percentage' => $this->calculateCompletionPercentage($user),
        'missing_fields' => $this->getMissingFields($user)
    ];
}
}
