<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Otp;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        $rules = [
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'phone_number' => ['required', 'string', 'max:20', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'otp_code' => ['required', 'string', 'max:10'],
            'role' => ['required', 'string', 'in:client,agency'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'license_number' => ['required', 'string', 'max:255'],
        ];

        // Add business_type validation if role is agency
        if ($data['role'] === 'agency') {
            $rules['business_type'] = ['required', 'string', 'max:255'];
        }

        return Validator::make($data, $rules);
    }

    protected function create(array $data)
    {
        $otp = Otp::where('phone_number', $data['phone_number'])
                  ->where('code', $data['otp_code'])
                  ->where('expired_at', '>', now())
                  ->first();

        if (!$otp) {
            return redirect()->back()->withErrors(['otp_code' => 'Invalid or expired OTP.']);
        }

        $otp->delete();

        $userData = [
            'username' => $data['username'],
            'phone_number' => $data['phone_number'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'license_number' => $data['license_number'],
        ];

        // Add business_type if role is agency
        if ($data['role'] === 'agency') {
            $userData['business_type'] = $data['business_type'];
        }

        return User::create($userData);
    }
}
