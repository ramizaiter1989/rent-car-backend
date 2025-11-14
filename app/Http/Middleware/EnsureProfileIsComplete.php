<?php

// ============================================================
// FILE: app/Http/Middleware/EnsureProfileIsComplete.php
// ============================================================
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsComplete
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Check if profile is complete
        if (!$this->isProfileComplete($user)) {
            return response()->json([
                'error' => 'Profile incomplete',
                'message' => 'Please complete your profile before accessing this resource',
                'missing_fields' => $this->getMissingFields($user),
                'redirect_to' => '/api/profile/complete'
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if user profile is complete based on role
     */
    private function isProfileComplete($user): bool
    {
        // Base required fields for all users
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

        // Role-specific checks
        switch ($user->role) {
            case 'client':
                return $this->isClientProfileComplete($user);
            
            case 'agency':
                return $this->isAgentProfileComplete($user);
            
            default:
                return true;
        }
    }

    /**
     * Check if client profile is complete
     */
    private function isClientProfileComplete($user): bool
    {
        $client = $user->client;
        
        if (!$client) {
            return false;
        }

        return !empty($client->license_number) &&
               !empty($client->driver_license) &&
               !empty($client->profession) &&
               !empty($client->avg_salary);
    }

    /**
     * Check if agent profile is complete
     */
    private function isAgentProfileComplete($user): bool
    {
        $agent = $user->agent;
        
        if (!$agent) {
            return false;
        }

        $baseComplete = !empty($agent->business_type) &&
                       !empty($agent->profession) &&
                       !empty($agent->location);

        // Additional checks based on business type
        if ($agent->business_type === 'company') {
            return $baseComplete && 
                   !empty($agent->company_number) &&
                   !empty($agent->business_doc);
        }

        return $baseComplete;
    }

    /**
     * Get list of missing fields for the user
     */
    private function getMissingFields($user): array
    {
        $missing = [];

        // Base fields
        if (empty($user->first_name)) $missing[] = 'first_name';
        if (empty($user->last_name)) $missing[] = 'last_name';
        if (empty($user->gender)) $missing[] = 'gender';
        if (empty($user->birth_date)) $missing[] = 'birth_date';
        if (empty($user->city)) $missing[] = 'city';
        if (empty($user->id_card_front)) $missing[] = 'id_card_front';
        if (empty($user->id_card_back)) $missing[] = 'id_card_back';

        // Role-specific fields
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
}