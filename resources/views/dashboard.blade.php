<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC]">
    <!-- Navigation -->
    <nav class="bg-white dark:bg-[#161615] border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold">Rento for Car Rental Admin</h1>
                </div>
                
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        {{ Auth::user()->first_name ?? Auth::user()->username }}
                    </span>
                    <span class="px-3 py-1 text-xs rounded-full bg-[#f53003] text-white">
                        {{ ucfirst(Auth::user()->role) }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-[#f53003] dark:text-[#FF4433] hover:underline">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <!-- Welcome Card -->
            <div class="bg-white dark:bg-[#161615] rounded-lg shadow-lg p-8 mb-6">
                <h2 class="text-2xl font-bold mb-2">
                    Welcome back, {{ Auth::user()->first_name ?? Auth::user()->username }}!
                </h2>
                <p class="text-[#706f6c] dark:text-[#A1A09A]">
                    You are logged in as <strong>{{ ucfirst(Auth::user()->role) }}</strong>
                </p>
            </div>

            <!-- User Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Profile Info -->
                <div class="bg-white dark:bg-[#161615] rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">Profile Information</h3>
                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="text-[#706f6c] dark:text-[#A1A09A]">Phone Number</dt>
                            <dd class="font-medium">{{ Auth::user()->phone_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-[#706f6c] dark:text-[#A1A09A]">Username</dt>
                            <dd class="font-medium">{{ Auth::user()->username }}</dd>
                        </div>
                        <div>
                            <dt class="text-[#706f6c] dark:text-[#A1A09A]">Status</dt>
                            <dd>
                                @if(Auth::user()->verified_by_admin)
                                    <span class="text-green-600">✓ Verified</span>
                                @else
                                    <span class="text-yellow-600">⚠ Pending Verification</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Account Status -->
                <div class="bg-white dark:bg-[#161615] rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">Account Status</h3>
                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="text-[#706f6c] dark:text-[#A1A09A]">Active</dt>
                            <dd>
                                @if(Auth::user()->status)
                                    <span class="text-green-600">● Active</span>
                                @else
                                    <span class="text-red-600">● Inactive</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-[#706f6c] dark:text-[#A1A09A]">OTP Verified</dt>
                            <dd>
                                @if(Auth::user()->otp_verification)
                                    <span class="text-green-600">✓ Yes</span>
                                @else
                                    <span class="text-red-600">✗ No</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-[#706f6c] dark:text-[#A1A09A]">Member Since</dt>
                            <dd class="font-medium">{{ Auth::user()->created_at->format('M d, Y') }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-[#161615] rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
                    <div class="space-y-2">
                        <a href="#" class="block px-4 py-2 text-sm bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded hover:bg-black dark:hover:bg-white transition-colors text-center">
                            Edit Profile
                        </a>
                        <a href="#" class="block px-4 py-2 text-sm border border-[#e3e3e0] dark:border-[#3E3E3A] rounded hover:border-black dark:hover:border-white transition-colors text-center">
                            View Settings
                        </a>
                        @if(Auth::user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm border border-[#f53003] text-[#f53003] rounded hover:bg-[#f53003] hover:text-white transition-colors text-center">
                                Admin Panel
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Role-Specific Content -->
            @if(Auth::user()->role === 'admin')
                <div class="bg-white dark:bg-[#161615] rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">Admin Dashboard</h3>
                    <p class="text-[#706f6c] dark:text-[#A1A09A]">
                        Access admin features and manage the system.
                    </p>
                </div>
            @elseif(Auth::user()->role === 'agency')
                <div class="bg-white dark:bg-[#161615] rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">Agent Dashboard</h3>
                    <p class="text-[#706f6c] dark:text-[#A1A09A]">
                        Manage your vehicles and bookings.
                    </p>
                </div>
            @elseif(Auth::user()->role === 'client')
                <div class="bg-white dark:bg-[#161615] rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">Client Dashboard</h3>
                    <p class="text-[#706f6c] dark:text-[#A1A09A]">
                        Browse vehicles and manage your bookings.
                    </p>
                </div>
            @endif
        </div>
    </main>
</body>
</html>